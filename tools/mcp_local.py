"""Cliente MCP HTTP local; autenticación privada y sesión MCP real."""
import base64,json,urllib.request
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]
class MCP:
    def __init__(self,auth_path=None):
        a=json.loads((Path(auth_path) if auth_path else ROOT/'local-private/mcp-auth.json').read_text())
        self.url=a['url'];self.serial=0
        self.headers={'Authorization':'Basic '+base64.b64encode((a['username']+':'+a['password']).encode()).decode(),
                      'Content-Type':'application/json','Accept':'application/json, text/event-stream','User-Agent':'MiniKids-MCP/1.0'}
        result=self.request('initialize',{'protocolVersion':'2025-06-18','capabilities':{},'clientInfo':{'name':'minikids-local','version':'1.0'}})
        self.headers['MCP-Protocol-Version']=result['protocolVersion']
        self.request('notifications/initialized',{},notification=True)
    def request(self,method,params,notification=False):
        self.serial+=1;body={'jsonrpc':'2.0','method':method,'params':params}
        if not notification:body['id']=self.serial
        req=urllib.request.Request(self.url,data=json.dumps(body).encode(),headers=self.headers)
        with urllib.request.urlopen(req,timeout=45) as r:
            if r.headers.get('Mcp-Session-Id'):self.headers['Mcp-Session-Id']=r.headers['Mcp-Session-Id']
            text=r.read().decode()
        if not text:return None
        if text.startswith('event:') or text.startswith('data:'):
            text='\n'.join(line[5:].lstrip() for line in text.splitlines() if line.startswith('data:'))
        result=json.loads(text)
        if 'error' in result:raise RuntimeError(result['error'])
        return result.get('result')
    def tools(self):return self.request('tools/list',{})['tools']
    def ability(self,name,parameters):
        result=self.call('mcp-adapter-execute-ability',{'ability_name':name,'parameters':parameters})
        if not result.get('success',False):raise RuntimeError(result)
        return result['data']
    def call(self,name,arguments):
        result=self.request('tools/call',{'name':name,'arguments':arguments})
        if result.get('isError'):raise RuntimeError(result)
        if 'structuredContent' in result:return result['structuredContent']
        for item in result.get('content',[]):
            if item.get('type')=='text':
                try:return json.loads(item['text'])
                except json.JSONDecodeError:return item['text']
        return result
if __name__=='__main__':
    client=MCP();abilities=client.call('mcp-adapter-discover-abilities',{})['abilities']
    print(json.dumps([t['name'] for t in abilities if t['name'].startswith('minikids/')],ensure_ascii=True))

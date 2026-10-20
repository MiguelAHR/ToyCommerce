from pathlib import Path
import subprocess,sys
from graphify.extract import extract
from graphify.build import build_from_json
from graphify.cluster import cluster
from graphify.export import to_json

def main():
    root=Path(__file__).resolve().parents[1]
    out=root/'brain/graphify-out';out.mkdir(parents=True,exist_ok=True)
    files=[p for p in (root/'tools').iterdir() if p.suffix in {'.php','.ps1','.py'}]
    files.extend(p for p in (root/'wordpress/wp-content/mu-plugins').iterdir() if p.suffix in {'.php','.js'})
    data=extract(files,cache_root=root/'brain',root=root,max_workers=2)
    concepts=[('attention','Atención, correo y edición humana','docs/ATENCION-CORREO-Y-EDICION.md'),('rules','Normas MCP, Blocksy y HPOS','AGENTS.md'),('project','ToyCommerce local actualizado','brain/STATE.md')]
    for key,label,source in concepts:
        data['nodes'].append({'id':'mk_'+key,'label':label,'file_type':'document','source_file':source,'source_location':'L1','_origin':'documented'})
    for a,b,relation in [('project','rules','follows'),('project','attention','implements')]:
        data['edges'].append({'source':'mk_'+a,'target':'mk_'+b,'relation':relation,'confidence':'EXTRACTED','source_file':'docs/ATENCION-CORREO-Y-EDICION.md','source_location':'L1','_origin':'documented','weight':1.0})
    for node in data['nodes'][:]:
        source=node.get('source_file','').replace('\\','/')
        if node.get('source_location')=='L1' and source.startswith('wordpress/wp-content/mu-plugins/minikids-'):
            data['edges'].append({'source':'mk_attention','target':node['id'],'relation':'implemented_in','confidence':'EXTRACTED','source_file':source,'source_location':'L1','_origin':'documented','weight':1.0})
    graph=build_from_json(data,directed=True,root=root)
    if not to_json(graph,cluster(graph),str(out/'graph.json')):raise RuntimeError('No se guardó el grafo')
    subprocess.run([sys.executable,'-m','graphify','export','html','--graph',str(out/'graph.json')],cwd=root,check=True)
    print('Memoria generada sin configuración ni SQL')

if __name__=="__main__":
    main()

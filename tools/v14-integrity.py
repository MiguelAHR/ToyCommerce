from pathlib import Path
R=Path(__file__).resolve().parents[1]
p=R/'tools/v13-integrity.py'
s=p.read_text('utf8').replace('v13-backups','v14-backups').replace('V13','V14')
s=s.replace("names=['minikids-commerce-v12.php','minikids-commerce-v12.css','minikids-commerce-v12.js','minikids-commerce-v9.php','minikids-commerce-v5.css','minikids-estetica.css']","names=['minikids-estetica.css','minikids-commerce-v9.php','minikids-commerce-v12.css','minikids-commerce-v9.css']")
exec(compile(s,str(p),'exec'),{'__file__':str(p),'__name__':'__main__'})

from pathlib import Path
import runpy
ROOT=Path(__file__).resolve().parents[1];OUT=ROOT.parent/'diseno_minikids/v5'
d=runpy.run_path(str(ROOT/'tools/v9-apply.py'));apply=d['apply']
for name in ['minikids-commerce-v9.php','minikids-commerce-v9.css','minikids-commerce-v9.js']:
    apply(name,(OUT/name).read_text('utf8'))

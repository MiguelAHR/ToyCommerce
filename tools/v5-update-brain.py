"""Use the existing Graphify updater with sequential extraction in this sandbox."""
from pathlib import Path
from importlib.machinery import SourceFileLoader
module=SourceFileLoader('minikids_update_brain',str(Path(__file__).with_name('update-brain.py'))).load_module()
original=module.extract
def sequential(*args,**kwargs):
    kwargs['parallel']=False
    return original(*args,**kwargs)
module.extract=sequential
if __name__=='__main__':module.main()

from pathlib import Path
R=Path(__file__).resolve().parents[1]
source=(R/'tools/v13-verify.py').read_text('utf8').replace('V13','V14').replace('v13-backups','v14-backups')
source=source.replace('source=source.replace(',"extra += (R/'tools/v14-checks.py').read_text('utf8')\nsource=source.replace(",1)
exec(compile(source,str(R/'tools/v13-verify.py'),'exec'),{'__file__':str(R/'tools/v13-verify.py'),'__name__':'__main__'})

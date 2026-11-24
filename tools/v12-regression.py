from pathlib import Path
R=Path(__file__).resolve().parents[1]
source=(R/'tools/v11-verify.py').read_text('utf8')
# Keep private V11 baseline; preserve every earlier evidence file. Integrity is checked separately in V12.
source=source.replace("O/f'V11-", "O/f'V12-regresion-").replace("O/'V11-", "O/'V12-regresion-").replace('verificacion-V11-', 'verificacion-V12-regresion-')
start=source.index("protected=json.loads((B/");end=source.index("out={'checks':",start)
source=source[:start]+"protected={};diff=[]\n"+source[end:]
exec(compile(source,str(R/'tools/v11-verify.py'),'exec'),{'__file__':str(R/'tools/v11-verify.py'),'__name__':'__main__'})

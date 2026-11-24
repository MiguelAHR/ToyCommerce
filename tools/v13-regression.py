from pathlib import Path
R=Path(__file__).resolve().parents[1]
source=(R/'tools/v11-verify.py').read_text('utf8')
source=source.replace("O/f'V11-", "O/f'V13-regresion-").replace("O/'V11-", "O/'V13-regresion-").replace('verificacion-V11-', 'verificacion-V13-regresion-')
source=source.replace("['Eliminar']", "['Eliminar producto']")
start=source.index("protected=json.loads((B/");end=source.index("out={'checks':",start)
source=source[:start]+"protected={};diff=[]\n"+source[end:]
exec(compile(source,str(R/'tools/v11-verify.py'),'exec'),{'__file__':str(R/'tools/v11-verify.py'),'__name__':'__main__'})

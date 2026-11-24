from pathlib import Path
import json
ROOT=Path(__file__).resolve().parents[1]; OUT=ROOT.parent/'diseno_minikids/v5'
# Each line defines a distinct physical toy, never a recolored reuse of another image.
groups=[
('peluches',18,3,'''Osito miel con lazo|small honey-brown teddy bear with cream cotton bow
Peluche alpaca andina|cream alpaca plush with long neck and tiny woven turquoise scarf
Peluche zorrito del bosque|rust orange fox plush with white chest and bushy tail
Peluche pingüino polar|round charcoal and white penguin plush with orange feet
Peluche capibara dormilón|tan sleeping capybara plush lying down
Peluche elefante nube|soft pale blue elephant plush with large floppy ears
Peluche tortuga marina|sea turtle plush with green hexagonal patterned shell
Peluche dinosaurio verde|sage green stegosaurus plush with ochre back plates
Peluche gatito moteado|cream and brown spotted kitten plush sitting upright
Peluche pulpo abrazos|lavender octopus plush with eight curled arms
Peluche leoncito melena|golden lion plush with fluffy russet mane'''),
('didacticos',19,3,'''Rompecabezas de formas de madera|natural wooden inset puzzle board with six chunky colorful geometric shapes
Clasificador casita de madera|wooden house shaped sorter with five bright chunky shape blocks
Arcoíris apilable de madera|seven nested wooden rainbow arches in muted primary colors
Laberinto de cuentas espiral|small natural wood bead maze with three colorful metal wire loops
Tablero de números encajables|wooden inset number puzzle board with digits zero through nine
Set de frutas para ensartar|six chunky wooden fruit beads with thick cotton threading cord
Reloj didáctico de madera|round wooden teaching clock with movable hands and twelve colored number blocks
Torre de equilibrio animalitos|wooden stacking balance toy with eight chunky animal silhouette pieces
Rompecabezas del océano|wooden inset ocean puzzle with fish crab whale seahorse turtle and starfish'''),
('vehiculos',21,3,'''Autobús escolar amarillo|unbranded chunky yellow toy school bus with black rolling wheels
Camión de bomberos con escalera|red toy fire truck with silver extending ladder
Tractor granjero con remolque|green toy farm tractor pulling small wooden cargo trailer
Avión de hélice de madera|small natural wooden toy airplane with red wings and blue propeller
Barco velero de juguete|toy wooden sailboat with cream fabric sail and teal hull
Excavadora de arena|yellow toy excavator with moving articulated digging arm
Ambulancia de rescate|white toy ambulance with orange stripes no emblems and red roof lights
Helicóptero de rescate|orange and navy toy helicopter with black rotor and landing skids
Auto de carreras rojo|sleek small unbranded red toy racing car with chunky black tires
Garaje de madera dos niveles|two level wooden toy parking garage with curved ramp and two small cars
Camión transportador de autos|blue toy car transporter truck carrying three tiny colorful cars'''),
('juegos-de-rol',20,3,'''Maletín de doctor de juguete|teal toy doctor case open with toy stethoscope thermometer and pretend syringe no needle
Cesta de verduras de juguete|small wicker style toy basket with six colorful pretend vegetables
Caja registradora de juguete|yellow and mint toy cash register with play coins and drawer
Set de herramientas mini taller|wooden toy tool box with hammer wrench screwdriver nuts and bolts
Delantal de chef con utensilios|small yellow striped child chef apron folded beside whisk spatula and rolling pin
Tetera y tacitas de juguete|pastel pink toy tea pot with two cups saucers and toy biscuits
Set de picnic infantil|small toy picnic basket with two plates cups and pretend sandwiches
Tostadora de madera|mint green wooden toy toaster with two wooden bread slices
Heladería de madera|small wooden tabletop pretend ice cream counter with three scoops and cones
Set de limpieza de juguete|child size pretend cleaning set with short broom dustpan mop and bucket
Puesto de frutas mini mercado|small tabletop wooden pretend market stand with fruit crates and fabric canopy
Taller de tornillos de madera|wooden toy workbench with vise hammer bolts and wrench'''),
('creatividad',22,4,'''Crayones lavables 12 colores|open cardboard tray of twelve colorful chunky wax crayons no readable brand
Acuarelas con pincel 12 colores|open watercolor paint palette twelve colors with one wooden brush
Plastilina creativa 6 colores|six open small tubs of colorful modeling dough with simple clay shapes
Set de sellos animalitos|six wooden rubber animal stamps with small colored ink pad
Pizarra de dibujo magnética|blue and yellow magnetic drawing board with attached stylus and three shape stamps
Kit de pulseras de cuentas|open organizer box with colorful craft beads and elastic cord
Set de origami de colores|stack of square colorful origami papers with folded paper crane and boat
Kit de pintura de piedras|craft kit of smooth stones with brightly painted ladybug and flower and brush
Set de tizas jumbo|open small box containing six jumbo sidewalk chalk sticks
Telar infantil de madera|small wooden weaving loom with colorful yarn and shuttle
Kit de mosaicos de espuma|foam craft mosaic kit with colorful square pieces and geometric picture board
Set de modelado con rodillo|modeling dough play set with wooden rolling pin four shape cutters and three clay balls'''),
('juegos-familiares',23,5,'''Dominó de animales de madera|wooden toy domino set with colorful animal pictures arranged in neat line
Ludo familiar de madera|wooden ludo board with colorful round pawns and dice
Juego de pesca magnética|small blue wooden toy pond with six colorful fish and two magnetic fishing rods
Torre de bloques de equilibrio|stacking tumble tower of natural wooden rectangular blocks with three pieces beside it
Bingo de figuras|family bingo game with four illustrated geometric shape cards colorful tokens and draw bag
Juego de bolos de madera|six colorful wooden toy bowling pins with two wooden balls
Tres en raya de madera|wooden tic tac toe board with chunky blue crosses and yellow circles
Juego de aros y objetivos|wooden ring toss game cross base with five pegs and three rope rings
Rompecabezas familiar 100 piezas|unbranded assembled 100 piece cardboard puzzle showing a colorful forest with few loose pieces
Juego de damas clásico|folding wooden checkerboard with black and natural wood checkers
Juego de equilibrio luna|wooden crescent moon balancing toy with colored cylindrical pieces and dice
Cartas de parejas frutitas|memory matching card game showing simple colorful fruit pairs no text
Juego de laberinto con canicas|wooden tabletop tilt maze with silver marble and two turning knobs'''),
('tecnologia',24,5,'''Robot explorador programable|small friendly unbranded white and teal toy robot with simple arrow button controller
Cámara infantil de juguete|small rounded yellow child toy digital camera with two large buttons and fabric strap
Walkie talkies exploradores|pair of chunky orange and navy child toy walkie talkies with short antennas
Microscopio de exploración infantil|simple teal and white child toy microscope with three blank slides
Linterna de proyección espacial|blue toy flashlight projector with three circular space picture discs
Teclado musical infantil|small unbranded red child keyboard with two octaves and yellow buttons
Tambor electrónico de juguete|compact child electronic drum pad with three colorful pads and two short sticks
Piano de madera con sonidos|small wooden toddler sound piano with eight colorful keys
Telescopio de explorador|small navy child toy telescope on low three leg tripod
Auto con control remoto|unbranded yellow toy remote control buggy with simple black handheld controller
Robot dinosaurio de juguete|unbranded small green robotic triceratops toy with visible joint details no weapons
Pizarra LCD de dibujo|turquoise child LCD drawing tablet with matching stylus and simple scribble on black screen
Set de circuitos inicial|child snap circuit toy with blue base battery holder small fan and LED components''')]
items=[]
for slug,cat,age,lines in groups:
    for line in lines.splitlines():
        name,subject=line.split('|');i=len(items);sku=f'MK-{i+18:03d}'
        regular=[29.9,39.9,49.9,59.9,79.9,99.9,129.9,149.9,199.9,249.9,19.9,9.9][i%12]
        # 24 new offers + 10 legacy offers = 34/98 (34.7%).
        sale=round(regular*(1-[.10,.15,.20,.25,.30][i%5]),2) if i<24 else None
        if slug=='peluches':regular=[29.9,39.9,49.9,59.9][i%4];sale=round(regular*(1-[.10,.15,.20,.25,.30][i%5]),2) if i<24 else None
        items.append({'sku':sku,'name':name,'category_slug':slug,'category_id':cat,'age':age,'subject':subject,'brand':'MiniKids','regular_price':f'{regular:.2f}','sale_price':f'{sale:.2f}' if sale else '', 'stock':([3,5,8,10,16,24,35,42][i%8]),'weight':'0.35' if slug=='peluches' else '0.65','dimensions':{'length':'25','width':'20','height':'18'},'gallery_required':i<20,'image':f'{sku}.png','price_status':'demo_local_no_validado_para_venta','spec_status':'dimensiones_peso_y_edad_demo_pendientes_de_validacion_del_dueno'})
assert len(items)==81
dest=OUT/'catalogo_plan.json'
if dest.exists():raise SystemExit('Plan already exists; preserve it')
dest.write_text(json.dumps(items,indent=2,ensure_ascii=False),encoding='utf8')
print('81 distinct demo specifications written')

<?php

use App\Services\ChordTransposer;

test('calcula semitonos entre dos tonalidades', function () {
    expect(ChordTransposer::semitonosEntre('A', 'G'))->toBe(-2);
    expect(ChordTransposer::semitonosEntre('G', 'A'))->toBe(2);
    expect(ChordTransposer::semitonosEntre('C', 'D'))->toBe(2);
    expect(ChordTransposer::semitonosEntre('C', 'B'))->toBe(-1);
    expect(ChordTransposer::semitonosEntre('F#', 'G'))->toBe(1);
    expect(ChordTransposer::semitonosEntre('Am', 'Gm'))->toBe(-2);
});

test('transpone acordes simples y compuestos', function () {
    expect(ChordTransposer::transponerAcorde('A', -2))->toBe('G');
    expect(ChordTransposer::transponerAcorde('F#m7', 1))->toBe('Gm7');
    expect(ChordTransposer::transponerAcorde('A/C#', -2))->toBe('G/B');
    expect(ChordTransposer::transponerAcorde('Dsus4', 2))->toBe('Esus4');
});

test('transpone texto con acordes en formato Cifra Club y corchetes', function () {
    $textoCifra = "[Intro]\nA  D  E\n\nA         D\nSé que mi Redentor vive\nE            A\nY al fin se levantará";
    $esperadoCifra = "[Intro]\nG  C  D\n\nG         C\nSé que mi Redentor vive\nD            G\nY al fin se levantará";

    expect(ChordTransposer::transponerTexto($textoCifra, -2))->toBe($esperadoCifra);

    $textoCorchetes = "[A]Sé que mi [D]Redentor vive\n[E]Y al fin se levantará";
    $esperadoCorchetes = "[G]Sé que mi [C]Redentor vive\n[D]Y al fin se levantará";

    expect(ChordTransposer::transponerTexto($textoCorchetes, -2))->toBe($esperadoCorchetes);
});

test('detecta encabezados de seccion musical correctamente', function () {
    expect(ChordTransposer::isSectionHeader('[Intro]'))->toBeTrue();
    expect(ChordTransposer::isSectionHeader('[Estrofa 1]'))->toBeTrue();
    expect(ChordTransposer::isSectionHeader('[Coro]'))->toBeTrue();
    expect(ChordTransposer::isSectionHeader('[Puente]'))->toBeTrue();
    expect(ChordTransposer::isSectionHeader('[Bridge]'))->toBeTrue();
    expect(ChordTransposer::isSectionHeader('[Intro] G D Em'))->toBeTrue();
    expect(ChordTransposer::isSectionHeader('Coro:'))->toBeTrue();
    expect(ChordTransposer::isSectionHeader('Estrofa 2'))->toBeTrue();

    // No debe detectar acordes o letras normales como encabezados
    expect(ChordTransposer::isSectionHeader('[C]'))->toBeFalse();
    expect(ChordTransposer::isSectionHeader('[F#m7]'))->toBeFalse();
    expect(ChordTransposer::isSectionHeader('[G] Sé que mi redentor vive'))->toBeFalse();
    expect(ChordTransposer::isSectionHeader('Solo en ti confío'))->toBeFalse();
    expect(ChordTransposer::isSectionHeader('Final de los tiempos'))->toBeFalse();
    expect(ChordTransposer::isSectionHeader(''))->toBeFalse();
});

test('divide lineas en dos columnas empezando la segunda columna con un encabezado de seccion', function () {
    $cancion = <<<'EOD'
[Intro]
G  D  Em  C

[Estrofa 1]
G              D
Sé que mi Redentor vive
Em             C
Y al fin se levantará

[Coro]
G           D
Yo sé que vive
Em          C
Mi Señor Jesús

[Estrofa 2]
G              D
Aunque pase por el fuego
Em             C
Él conmigo estará

[Coro]
G           D
Yo sé que vive
Em          C
Mi Señor Jesús
EOD;

    [$col1, $col2] = ChordTransposer::dividirLineasEnDosColumnas(explode("\n", $cancion));

    // La columna 2 no debe estar vacía
    expect($col2)->not->toBeEmpty();

    // La primera línea de la columna 2 DEBE ser un encabezado de sección
    $primeraLineaCol2 = trim($col2[0]);
    expect(ChordTransposer::isSectionHeader($primeraLineaCol2))->toBeTrue();
    expect(in_array($primeraLineaCol2, ['[Coro]', '[Estrofa 2]'], true))->toBeTrue();

    // Verificar que ambas columnas tienen un balance razonable de líneas
    $totalCol1 = count($col1);
    $totalCol2 = count($col2);
    expect(abs($totalCol1 - $totalCol2))->toBeLessThanOrEqual(6);
});

test('divide canciones sin encabezados usando saltos de estrofa', function () {
    $cancionSinHeaders = <<<'EOD'
Línea 1
Línea 2

Línea 3
Línea 4
EOD;

    [$col1, $col2] = ChordTransposer::dividirLineasEnDosColumnas(explode("\n", $cancionSinHeaders));

    expect($col1)->toBe(['Línea 1', 'Línea 2']);
    expect($col2)->toBe(['Línea 3', 'Línea 4']);
});

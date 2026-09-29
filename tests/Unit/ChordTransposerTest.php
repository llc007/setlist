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

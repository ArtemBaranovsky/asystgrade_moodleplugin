<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * This file contains the essay questions for the local_asystgrade plugin.
 *
 * @package   local_asystgrade
 * @copyright 2024 Artem Baranovskyi <artem.baranovsky1980@gmail.com>
 * @copyright Based on work by 2023 Ulrike Pado <ulrike.pado@hft-stuttgart.de>,
 *            Yunus Eryilmaz & Larissa Kirschner <https://link.springer.com/article/10.1007/s40593-023-00383-w>
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

return [
    [
        'questiontext' => 'Warum kann Ihr Programm durch die Verwendung von Threads schneller werden, auch wenn Sie nur einen
                            einzigen Prozessor zur Verfügung haben?',
        'qtype'        => 'essay',
        'answers'      => [
            'Die Ausführung wird schneller, weil der Prozessor zwischen verschiedenen Teilaufgaben hin- und herspringen kann und
             so z.B. Wartezeiten auf Daten in einem Thread zur Bearbeitung anderer Threads genutzt werden können.' => 1,
        ],
    ],
    [
        'questiontext' => 'Warum wird Ihr Programm durch die Verwendung von Threads schneller, auch wenn Sie nur einen
                            einzigen Prozessor zur Verfügung haben?',
        'qtype'        => 'essay',
        'answers'      => [
            'Die Ausführung wird schneller, weil der Prozessor zwischen verschiedenen Teilaufgaben hin- und herspringen kann und
             so z.B. Wartezeiten auf Daten in einem Thread zur Bearbeitung anderer Threads genutzt werden können.' => 1,
        ],
    ],
    [
        'questiontext' => 'Beschreiben Sie die Struktur einer Stream-Pipeline. Woher kommen die Daten, was geschieht im Stream
                            mit ihnen, wie endet der Stream? Geben Sie für jeden Streamabschnitt mindestens eine
                            Beispielkomponente an.',
        'qtype'        => 'essay',
        'answers'      => [
            'Datenquelle: Collections, Arrays, Generatoren (z.B. Datenbankabfragen, eigene Methoden). Verarbeitung: Filtern,
             Umformung, Begrenzung. Datensenke: Minimum / Maximum / Durchschnitt / Anzahl, Ausgabe in Collection oder Array /
             Reduktion / Auswertung.' => 1,
        ],
    ],
    [
        'questiontext' => 'Welche Auswirkungen hat die Model-View-Aufteilung bei Swing-Komponenten? Nennen Sie Beispiele anhand
                            der Klasse JTable.',
        'qtype'        => 'essay',
        'answers'      => [
            'Datenhaltung und Darstellung werden getrennt, so dass dieselben Daten flexibel dargestellt werden können. Datenhaltung
             findet in der Klasse TableModel statt: Welche Information steht in welcher Zelle? Information z.B. über die
             Editierbarkeit der Zellen, den zu verwendenden Editor und die Spaltenreihenfolge werden in JTable bzw.
             TableColumnModel gehalten.' => 3,
        ],
    ],
    [
        'questiontext' => 'Warum braucht man bei der Arbeit mit Threads Synchronisation?',
        'qtype'        => 'essay',
        'answers'      => [
            'Man muss vermeiden, dass verschiedene Threads gleichzeitig auf Daten oder Objekte zugreifen, weil es dadurch zur
             Zerstörung von Werten und zu inkonsistenten Zuständen kommen kann.' => 1,
        ],
    ],
    [
        'questiontext' => 'Beantworten Sie kurz die 3 Fragen 1 - Was wird unter einem Thread verstanden / wann werden Threads
                            benutzt ? (3 Pkt) 2 - Welche Ressourcen nutzt ein Thread exklusiv ? (2 Pkt) 3 - Welche Ressourcen
                            teilen sich die Threads eines Programms ? (2 Pkt)',
        'qtype'        => 'essay',
        'answers'      => [
            'Thread: 1 PT Ablauffaden, Ablaufeinheit, Ausführung etc. 1Pt Parallel, Quasi Parallel, Core, 1Pt Geschwindigkeit,
             Resourcenauslastung, parallele Ausführung' => 7,
        ],
    ],
];

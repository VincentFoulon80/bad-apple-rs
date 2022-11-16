<?php

// Encodes the source file to a count of how many characters to print before switching to the other

$source = file_get_contents('src/source.txt');
[$FRAMES, $ROWS, $COLS] = getSize($source);

const BITS = 8;
const BIT_LIMIT = 2 ** BITS;


$bit = true;
$chrCount = 0;
$DATA = [];
$i = 0;
foreach (str_split($source) as $chr) {
    if (($bit && $chr == '-') || (!$bit && $chr == '#')) {
        $bit = $chr == '#';
        if ($chrCount >= BIT_LIMIT) {
            $timesOverflow = floor($chrCount / (BIT_LIMIT - 1));
            $rest = $chrCount % (BIT_LIMIT - 1);
            for ($i = 0; $i < $timesOverflow; $i++) {
                $DATA[] = BIT_LIMIT - 1;
                $DATA[] = 0;
            }
            $DATA[] = $rest;
        } else {
            $DATA[] = $chrCount;
        }

        $chrCount = 0;
    }
    if ($chr === '-' || $chr === '#') {
        $chrCount++;
    }
    $i++;
}
if ($chrCount > 0) {
    if ($chrCount >= BIT_LIMIT) {
        $timesOverflow = floor($chrCount / (BIT_LIMIT - 1));
        $rest = $chrCount % (BIT_LIMIT - 1);
        for ($i = 0; $i < $timesOverflow; $i++) {
            $DATA[] = BIT_LIMIT - 1;
            $DATA[] = 0;
        }
        $DATA[] = $rest;
    } else {
        $DATA[] = $chrCount;
    }
}

$DATACOUNT = count($DATA);
$DATA = implode(', ', $DATA);
$BITS = BITS;

$output = "pub const ROWS: usize = ${ROWS};\n";
$output .= "pub const COLS: usize = ${COLS};\n";
$output .= "pub const FRAMES: usize = ${FRAMES};\n";
$output .= "pub const STREAM: [u${BITS}; ${DATACOUNT}] = [${DATA}];";

file_put_contents('src/data.rs', $output);



function getSize(string $source)
{
    $frames = explode("\n\n", $source);
    $framesLength = count($frames);
    $rows = explode("\n", $frames[0]);
    $rowsLength = count($rows);
    $cols = $rows[0];
    $colsLength = strlen($cols);
    return [$framesLength, $rowsLength, $colsLength];
}

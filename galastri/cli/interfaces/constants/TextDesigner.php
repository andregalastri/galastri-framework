<?php

namespace galastri\cli\interfaces\constants;

interface TextDesigner
{
    const LAYOUT_SIZE = 70;

    const BOX_STYLES = [
        //                      0    1    2    3    4    5    6    7
        'thin'             => ["┌", "┐", "└", "┘", "─", "│", "├", "┤"],
        'doubled'          => ["╔", "╗", "╚", "╝", "═", "║", "╠", "╣"],
    ];
}

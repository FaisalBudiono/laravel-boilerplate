<?php

declare(strict_types=1);

namespace App\Core\Date;

enum DatetimeFormat: string
{
    case ISO_WITH_MILLIS = 'Y-m-d\TH:i:s.vp';
}

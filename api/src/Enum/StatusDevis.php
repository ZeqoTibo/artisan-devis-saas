<?php

namespace App\Enum;

enum StatusDevis: string
{
    case Created = 'created';
    case Sent = 'sent';
    case Opened = 'opened';
    case InDiscussion = 'in_discussion';
    case Signed = 'signed';
    case Lost = 'lost';
}

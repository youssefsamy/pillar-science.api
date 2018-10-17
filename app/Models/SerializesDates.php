<?php

namespace App\Models;

trait SerializesDates
{
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d\TH:i:sO');
    }
}
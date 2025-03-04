<?php

function money($figure)
{
    return auth()->user()->business->currency.' '.number_format($figure, 2, '.', ',');
}

function easyCount($figure)
{
    return number_format($figure, 0, null, ',');
}

function easyCountDecimal($figure)
{
    return number_format($figure, 2, '.', ',');
}
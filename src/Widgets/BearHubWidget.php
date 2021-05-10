<?php

namespace Michavie\Bearhub\Widgets;

use Statamic\Widgets\Widget;

class BearHubWidget extends Widget
{
    protected static $handle = 'bearhub';

    public function html()
    {
        return view('bearhub::main-widget', []);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Publisher\Http\Controllers\Frontend;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\PublisherController as BasePublisherController;

class PublisherController extends Controller
{
    protected BasePublisherController $base;

    public function __construct()
    {
        $this->base = new BasePublisherController();
    }

    public function index(Request $request)
    {
        return $this->base->index($request);
    }

    public function show(string $slug)
    {
        return $this->base->show($slug);
    }
}

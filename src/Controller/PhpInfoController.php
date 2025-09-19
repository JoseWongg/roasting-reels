<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Execute the route in the browser to get the phpinfo
 */
class PhpInfoController
{

    #[Route('/phpinfo', name: 'phpinfo')]
    public function index(): Response
    {
        ob_start();
        phpinfo();
        $phpinfo = ob_get_clean();

        return new Response($phpinfo);
    }
}
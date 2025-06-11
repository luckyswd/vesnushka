<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TrailingSlashRedirectListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        if (HttpKernelInterface::MAIN_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            return;
        }

        $pathInfo = $request->getPathInfo();

        if ('/' !== $pathInfo && str_ends_with($pathInfo, '/')) {
            $cleanPath = rtrim($pathInfo, '/');

            $newUrl = $request->getSchemeAndHttpHost().$cleanPath;

            if ($request->getQueryString()) {
                $newUrl .= '?'.$request->getQueryString();
            }

            $response = new RedirectResponse($newUrl, 301);

            $event->setResponse($response);
        }
    }
}

<?php

namespace App\EventListener;

use App\Attribute\SkipCsrf;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\HttpFoundation\Request;

class CsrfRequestListener implements EventSubscriberInterface
{
  private CsrfTokenManagerInterface $csrfTokenManager;

  public function __construct(CsrfTokenManagerInterface $csrfTokenManager)
  {
    $this->csrfTokenManager = $csrfTokenManager;
  }

  public function onKernelController(ControllerEvent $event): void
  {
    $request = $event->getRequest();

    // Vérifie si on est bien dans une API (routes commençant par /api/)
    if (!str_starts_with($request->getPathInfo(), '/api/')) {
      return;
    }

    // Ignorer les requêtes GET (pas besoin de CSRF)
    if ($request->isMethod(Request::METHOD_GET)) {
      return;
    }

    // Vérifie si le contrôleur possède l'attribut #[SkipCsrf]
    $controller = $event->getController();
    if (is_array($controller)) {
      $reflectionMethod = new \ReflectionMethod($controller[0], $controller[1]);
      if (!empty($reflectionMethod->getAttributes(SkipCsrf::class))) {
        return; // On sort, pas besoin de CSRF ici
      }
    }

    // Vérification du CSRF (si nécessaire)
    $csrfToken = $request->headers->get('X-CSRF-TOKEN');
    if (!$csrfToken || !$this->csrfTokenManager->isTokenValid(new CsrfToken('authenticate', $csrfToken))) {
      throw new InvalidCsrfTokenException('Invalid CSRF token.');
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [
      ControllerEvent::class => 'onKernelController',
    ];
  }
}

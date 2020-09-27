<?php


namespace Drupal\offer_to_subscribe\EventSubscriber;


use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class OfferToSubscribeGreetingSubscriber
 * @todo add description class
 *
 * @package Drupal\offer_to_subscribe\EventSubscriber
 */
class OfferToSubscribeGreetingSubscriber implements EventSubscriberInterface {
  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityTypeManager;

  /**
   * OfferToSubscribeGreetingSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(AccountProxyInterface $currentUser, RouteMatchInterface $routeMatch, EntityTypeManagerInterface $entityTypeManager) {
    $this->currentUser = $currentUser;
    $this->routeMatch = $routeMatch;
    $this->entityTypeManager = $entityTypeManager->getStorage('user');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 0];

    return $events;
  }

  /**
   * @todo add description method
   */
  public function onRequest(GetResponseEvent $event) {
    /** @var \Drupal\user\UserInterface $user */
    $user = $this->entityTypeManager->load($this->currentUser->id());

    if ($user->hasRole('administrator') || $user->isAnonymous()) {
      return;
    }

    if ($user->isAuthenticated() && $node = $this->routeMatch->getParameter('node')) {
      $type_name = $node->bundle();
      if ($type_name == 'group') {
        $parameters = [
          'entity_type_id' => $node->getEntityTypeId(),
          'group' => $node->id(),
        ];
        $message = $this->t('Hi %user_name, click <a href=":link">here</a> if you would like to subscribe to this group called %group_title', ['%user_name' => $this->currentUser->getAccountName(), '%group_title' => $node->getTitle(), ':link' => Url::fromRoute('og.subscribe', $parameters)->toString()]);
        $this->messenger()->addMessage($message);
      }
    }

  }

}

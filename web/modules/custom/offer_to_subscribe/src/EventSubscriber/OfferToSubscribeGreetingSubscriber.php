<?php


namespace Drupal\offer_to_subscribe\EventSubscriber;


use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\og\Og;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class OfferToSubscribeGreetingSubscriber
 * Defines the method when subscribing to a group visit.
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
   * OfferToSubscribeGreetingSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $currentUser
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   */
  public function __construct(AccountProxyInterface $currentUser, RouteMatchInterface $routeMatch) {
    $this->currentUser = $currentUser;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['onRequest', 0];

    return $events;
  }

  /**
   * The method produces a proposal to issue group membership.
   */
  public function onRequest(GetResponseEvent $event) {

    $user_roles = $this->currentUser->getRoles();
    if (in_array('administrator', $user_roles) || $this->currentUser->isAnonymous()) {
      return;
    }

    if ($this->currentUser->isAuthenticated() && ($node = $this->routeMatch->getParameter('node'))) {
      $type_name = $node->bundle();
      if ($type_name == 'group' && !Og::isMember($node, $this->currentUser)) {
        $parameters = [
          'entity_type_id' => $node->getEntityTypeId(),
          'group' => $node->id(),
        ];
        $message = $this->t('Hi %user_name, click <a href=":link">here</a> if you would like to subscribe to this group called %group_title',
          [
            '%user_name' => $this->currentUser->getAccountName(),
            '%group_title' => $node->getTitle(),
            ':link' => Url::fromRoute('og.subscribe', $parameters)->toString(),
          ]
        );
        $this->messenger()->addMessage($message);
      }
    }

  }

}

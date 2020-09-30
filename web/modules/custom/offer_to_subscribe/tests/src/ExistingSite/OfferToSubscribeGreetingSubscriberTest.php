<?php


namespace Drupal\Tests\offer_to_subscribe\ExistingSite;


use weitzman\DrupalTestTraits\ExistingSiteBase;

/**
 * Class OfferToSubscribeGreetingSubscriberTest
 *
 * @package Drupal\Tests\offer_to_subscribe\ExistingSite
 */
class OfferToSubscribeGreetingSubscriberTest extends ExistingSiteBase {

  public function testGroupSubscribe() {
    $user = $this->createUser();
  }

}

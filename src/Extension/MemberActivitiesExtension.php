<?php

namespace HudhaifaS\Extension;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\GroupedList;
use SilverStripe\Security\Security;
use SilverStripe\Versioned\Versioned;
use SilverStripe\View\Requirements;

/**
 *
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, May 23, 2018 - 13:26:35 PM
 */
class MemberActivitiesExtension
        extends DataExtension {

    private static $allowed_actions = [];
    private static $tracked_classes = [];

    public function onAfterInit() {
        Requirements::css("hudhaifas/silverstripe-member-ownership: res/css/timeline.css");

        if ($this->owner->hasMethod('isRTL') && $this->owner->isRTL()) {
            Requirements::css("hudhaifas/silverstripe-member-ownership: res/css/timeline-rtl.css");
        }
    }

    public function getLastCreatedObjects($limit = 12) {
        $list = ArrayList::create([]);

        foreach ($this->owner->config()->tracked_classes as $clazz) {
            $list->merge($this->getLastCreatedObject($clazz, $limit));
        }

//        return $list;
        return GroupedList::create($list
                                ->sort('Created DESC')
                                ->limit($limit)
        );
    }

    public function getLastCreatedObject($clazz, $limit) {
        $member = Security::getCurrentUser();

        if (!$member) {
            return;
        }

        return Versioned::get_by_stage($clazz, Versioned::LIVE)
                        ->filter([
                            'CreatedByID' => $member->ID
                        ])
                        ->sort('Created', 'DESC')
                        ->limit($limit);
    }

    public function getLastEditedObjects($limit = 12) {
        $list = ArrayList::create([]);

        foreach ($this->owner->config()->tracked_classes as $clazz) {
            $list->merge($this->getLastEditedObject($clazz, $limit));
        }

//        return $list;
        return GroupedList::create($list
                                ->sort('LastEdited DESC')
                                ->limit($limit)
        );
    }

    public function getLastEditedObject($clazz, $limit) {
        $member = Security::getCurrentUser();

        if (!$member) {
            return;
        }

        return Versioned::get_by_stage($clazz, Versioned::LIVE)
                        ->filter([
                            'EditedByID' => $member->ID
                        ])
                        ->sort('LastEdited', 'DESC')
                        ->limit($limit);
    }

}

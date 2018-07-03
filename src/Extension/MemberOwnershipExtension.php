<?php

namespace HudhaifaS\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Member;

/**
 *
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, Mar 3, 2017 - 9:33:52 AM
 */
class MemberOwnershipExtension
        extends DataExtension {

    private static $has_one = [
        'CreatedBy' => Member::class,
        'EditedBy' => Member::class,
    ];

    /**
     * Field used to hold flag indicating the next write should be without a new ownership
     */
    const NEXT_WRITE_WITHOUT_OWNERSHIP = 'NextWriteWithoutOwnership';

    /**
     * Ensure versioned page doesn't attempt to virtualise these non-db fields
     *
     * @config
     * @var array
     */
    private static $non_virtual_fields = [
        self::NEXT_WRITE_WITHOUT_OWNERSHIP,
    ];

    public function updateSummaryFields(&$fields) {
        $fields['CreatedBy.Title'] = _t('DataObjectExtension.CREATED_BY', 'Created By');
        $fields['EditedBy.Title'] = _t('DataObjectExtension.EDITED_BY', 'Edited By');
    }

    public function updateFieldLabels(&$labels) {
        $labels['CreatedBy'] = _t('DataObjectExtension.CREATED_BY', 'Created By');
        $labels['CreatedBy.Title'] = _t('DataObjectExtension.CREATED_BY', 'Created By');

        $labels['EditedBy'] = _t('DataObjectExtension.EDITED_BY', 'Edited By');
        $labels['EditedBy.Title'] = _t('DataObjectExtension.EDITED_BY', 'Edited By');
    }

    public function updateCMSFields(FieldList $fields) {
        $fields->removeFieldFromTab('Root.Main', 'CreatedByID');
        $fields->removeFieldFromTab('Root.Main', 'EditedByID');
    }

    /**
     * Perform a write without affecting the ownership.
     *
     * @return int The ID of the record
     */
    public function writeWithoutOwnership() {
        $this->setNextWriteWithoutOwnership(true);

        return $this->owner->write();
    }

    public function onBeforeWrite() {
        if ($this->getNextWriteWithoutOwnership()) {
            return;
        }

        if (Member::currentUserID()) {
            if (!$this->owner->CreatedByID) {
                $this->owner->CreatedByID = Member::currentUserID();
            }

            $this->owner->EditedByID = Member::currentUserID();
        }
    }

    public function onAfterWrite() {
        $this->setNextWriteWithoutOwnership(false);
    }

    /**
     * Check if next write is without ownership
     *
     * @return bool
     */
    public function getNextWriteWithoutOwnership() {
        return $this->owner->getField(self::NEXT_WRITE_WITHOUT_OWNERSHIP);
    }

    /**
     * Set if next write should be without ownership or not
     *
     * @param bool $flag
     * @return DataObject owner
     */
    public function setNextWriteWithoutOwnership($flag) {
        return $this->owner->setField(self::NEXT_WRITE_WITHOUT_OWNERSHIP, $flag);
    }

    /**
     * Returns the month name this news item was posted in.
     * @return string
     */
    public function getMonthCreated() {
        return date('F', strtotime($this->owner->Created));
    }

    public function getMonthLastEdited() {
        return date('F', strtotime($this->owner->LastEdited));
    }

    public function getDayCreated() {
        return date('F d, Y', strtotime($this->owner->Created));
    }

    public function getDayLastEdited() {
        return date('F d, Y', strtotime($this->owner->LastEdited));
    }

}

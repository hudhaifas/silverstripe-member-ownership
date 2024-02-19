<?php

namespace HudhaifaS\Extension;

use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Security;

/**
 *
 * @author Hudhaifa Shatnawi <hudhaifa.shatnawi@gmail.com>
 * @version 1.0, Mar 3, 2017 - 9:33:52 AM
 */
class MemberLastEditedExtension
        extends DataExtension {

    /**
     * Field used to hold flag indicating the next write should be without a changing LastEdited field
     */
    const NEXT_WRITE_WITHOUT_LASTEDITED = 'NextWriteWithoutLastEdited';
    const PREV_LASTEDITED_FIELD = 'PrevLastEdited';

    /**
     * Ensure versioned page doesn't attempt to virtualise these non-db fields
     *
     * @config
     * @var array
     */
    private static $non_virtual_fields = [
        self::NEXT_WRITE_WITHOUT_LASTEDITED,
        self::PREV_LASTEDITED_FIELD,
    ];

    /**
     * Perform a write without affecting the LastEdited field.
     *
     * @return int The ID of the record
     */
    public function writeWithoutLastEdited() {
        $this->setNextWriteWithoutLastEdited(true);

        return $this->owner->write();
    }

    public function onBeforeWrite() {
        if ($this->getNextWriteWithoutLastEdited()) {
            $this->savePrevLastEdited();
        }
    }

    public function onAfterWrite() {
        if ($this->getNextWriteWithoutLastEdited()) {
            $this->restorePrevLastEdited();
        }

        $this->setNextWriteWithoutLastEdited(false);
    }

    /**
     * Checks if next write is without changing the LastEdited field
     *
     * @return bool
     */
    public function getNextWriteWithoutLastEdited() {
        return $this->owner->getField(self::NEXT_WRITE_WITHOUT_LASTEDITED);
    }

    /**
     * Sets if next write should be without changing LastEdited field or not
     *
     * @param bool $flag
     * @return DataObject owner
     */
    public function setNextWriteWithoutLastEdited($flag) {
        return $this->owner->setField(self::NEXT_WRITE_WITHOUT_LASTEDITED, $flag);
    }

    /**
     * Returns the previous LastEdited field
     *
     * @return bool
     */
    public function getPrevLastEdited() {
        return $this->owner->getField(self::PREV_LASTEDITED_FIELD);
    }

    /**
     * Saves the recent LastEdited field
     *
     * @return bool
     */
    public function savePrevLastEdited() {
        return $this->owner->setField(self::PREV_LASTEDITED_FIELD, $this->owner->LastEdited);
    }

    /**
     * Restores the previous LastEdited field
     */
    public function restorePrevLastEdited() {
        $this->owner->LastEdited = $this->getPrevLastEdited();

        // Finds the specific class that directly holds the given field and returns the table
        $table = DataObject::getSchema()->tableForField($this->owner->ClassName, 'LastEdited');

        if (Security::database_is_ready()) {
            DB::prepared_query(
                    sprintf('UPDATE "%s" SET "LastEdited" = ? WHERE "ID" = ?', $table), [
                $this->owner->LastEdited,
                $this->owner->ID
            ]);
        }
    }

}

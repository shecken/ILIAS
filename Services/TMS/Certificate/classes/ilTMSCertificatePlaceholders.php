<?php

require_once('./Services/User/classes/class.ilObjUser.php');

/**
 * Append placeholders to certificates.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilTMSMailRecipient implements Mailing\Recipient {
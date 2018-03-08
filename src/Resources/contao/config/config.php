<?php
/**
 * Copyright (c) 2018 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['replaceInsertTags']['huh.request'] = ['huh.request.insertTagsListener', 'onReplaceInsertTags'];
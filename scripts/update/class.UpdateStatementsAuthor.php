<?php
/**
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 *
 */

/**
 * Updates the author in the statements table to the uri
 * of the user instead of his login.
 * 
 * Executed after the instalation of the new extensions because of
 * a bug in the extension installer
 * 
 * @author bout
 *
 */
class taoUpdate_scripts_update_UpdateStatementsAuthor extends tao_scripts_Runner {
    
    public function run(){
        $dbWrapper = core_kernel_classes_DbWrapper::singleton();
        $sth = $dbWrapper->prepare('SELECT DISTINCT "author" FROM statements');
        $result = $sth->execute();
        
        $updateQuery = $dbWrapper->prepare('UPDATE statements SET "author" = ? WHERE "author" = ?;');
        foreach ($sth->fetchAll() as $data) {
            if (!empty($data['author'])) {
                $login = $data['author'];
                $user = core_kernel_users_Service::singleton()->getOneUser($login);
                if (is_null($user)) {
                    $this->err('User with login '.$login.' not found');
                } else {
                    if (!$updateQuery->execute(array($user->getUri(), $login))) {
                        $this->err('Unable to replace '.$login.' with '.$user->getUri());
                    }
                }
            }
        }
    }
}
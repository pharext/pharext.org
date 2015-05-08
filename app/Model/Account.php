<?php

namespace app\Model;

use pq\Gateway\Row;

class Account extends Row
{
	function updateToken($authority, $token, $json) {
		$tokens = $this->table->getTokens();
		
		$existing = $tokens->find([
			"authority=" => $authority,
			"account=" => $this->account,
		]);
		
		if (count($existing)) {
			$found = $existing->current();
			$found->token = $token;
			$found->oauth = $json;
			$found->update();
			return $found;
		}
		
		return $tokens->create([
			"authority" => $authority,
			"account" => $this->account,
			"token" => $token,
			"oauth" => $json
		])->current();
	}
	
	function updateOwner($authority, $user, $json) {
		$owners = $this->table->getOwners();
		
		$existing = $owners->find([
			"authority=" => $authority,
			"account=" => $this->account,
		]);
		
		if (count($existing)) {
			$found = $existing->current();
			$found->login = $user;
			$found->owner = $json;
			$found->update();
			return $found;
		}
		
		return $owners->create([
			"authority" => $authority,
			"login" => $user,
			"owner" => $json
		])->current();
	}
}

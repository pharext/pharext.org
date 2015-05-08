<?php

namespace app\Model;

use pq\Connection;
use pq\Gateway\Table;
use pq\Query\Expr;

class Accounts extends Table
{
	protected $rowset = "app\\Model\\AccountCollection";
	
	/**
	 * @var \app\Model\Tokens
	 */
	private $tokens;
	
	/**
	 * @var \app\Model\Owners
	 */
	private $owners;
	
	function __construct(Connection $conn, Tokens $tokens, Owners $owners) {
		parent::__construct("accounts", $conn);
		$this->tokens = $tokens;
		$this->owners = $owners;
	}
	
	function getTokens() {
		return $this->tokens;
	}
	
	function getOwners() {
		return $this->owners;
	}
	
	function byOAuth($authority, $token, $user) {
		if (!($account = $this->byOAuthToken($authority, $token))) {
			$account = $this->byOAuthOwner($authority, $user);
		}
		return $account;
	}
	
	function byOAuthToken($authority, $access_token, &$token = null) {
		$tokens = $this->tokens->find([
			"token=" => $access_token,
			"authority=" => $authority,
		]);
		
		if (count($tokens)) {
			$token = $tokens->current();
			$accounts = $this->by($token);
			if (count($accounts)) {
				return $accounts->current();
			}
		}
	}
	
	function byOAuthOwner($authority, $login, &$owner = null) {
		$owners = $this->owners->find([
			"authority=" => $authority,
			"login=" => $login,
		]);
		
		if (count($owners)) {
			$owner = $owners->current();
			$accounts = $this->by($owner);
			if (count($accounts)) {
				return $accounts->current();
			}
		}
	}
	
	function createOAuthAccount($authority, $token, $user) {
		$account = $this->create([
			"account" => new Expr("DEFAULT")
		])->current();
		$this->owners->create([
			"account" => $account->account,
			"authority" => $authority,
			"login" => $user,
		]);
		$this->tokens->create([
			"account" => $account->account,
			"authority" => "github",
			"token" => $token
		]);
		return $account;
	}
}

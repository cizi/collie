<?php

namespace App\Model;

use Nette;
use App\Model\Entity\UserEntity;
use Nette\InvalidStateException;
use Nette\Security\Passwords;

class UserRepository extends BaseRepository implements Nette\Security\IAuthenticator {

	const PASSWORD_COLUMN = 'password';

	/**
	 * Performs an authentication.
	 *
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials) {
		$email = (isset($credentials['email']) ? $credentials['email'] : "");
		$password = (isset($credentials['password']) ? $credentials['password'] : "");

		$query = ["select * from user where email = %s", $email, " and active = 1"];
		$row = $this->connection->query($query)->fetch();

		if (!$row) {
			throw new Nette\Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);
		} elseif (!Passwords::verify($password, $row[self::PASSWORD_COLUMN])) {
			throw new Nette\Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
		}

		$userEntity = new UserEntity();
		$userEntity->hydrate($row->toArray());

		$arr = $row->toArray();
		unset($arr[self::PASSWORD_COLUMN]);

		return new Nette\Security\Identity($userEntity->getId(), $userEntity->getRole(), $arr);
	}

	/**
	 * @return UserEntity[]
	 */
	public function findUsers() {
		$query = "select * from user";
		$result = $this->connection->query($query);

		$users = [];
		foreach ($result->fetchAll() as $row) {
			$user = new UserEntity();
			$user->hydrate($row->toArray());
			$users[] = $user;
		}

		return $users;
	}

	/**
	 * @param int $id
	 * @return UserEntity
	 */
	public function getUser($id) {
		$query = ["select * from user where id = %i", $id];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$userEntity = new UserEntity();
			$userEntity->hydrate($row->toArray());
			return $userEntity;
		}
	}

	/**
	 * @param int $id
	 * @return UserEntity
	 */
	public function getUserByEmail($email) {
		$query = ["select * from user where email = %s", $email];
		$row = $this->connection->query($query)->fetch();
		if ($row) {
			$userEntity = new UserEntity();
			$userEntity->hydrate($row->toArray());
			return $userEntity;
		}
	}

	/**
	 * @param $id
	 * @return bool
	 */
	public function deleteUser($id) {
		$return = false;
		if (!empty($id)) {
			$query = ["delete from user where id = %i", $id ];
			$return = ($this->connection->query($query) == 1 ? true : false);
		}

		return $return;
	}

	/**
	 * @param UserEntity $userEntity
	 */
	public function saveUser(UserEntity $userEntity) {
		if ($userEntity->getId() == null) {
			$userEntity->setLastLogin('0000-00-00 00:00:00');
			$userEntity->setRegisterTimestamp(date('Y-m-d H:i:s'));
			$query = ["insert into user ", $userEntity->extract()];
		} else {
			$updateArray = $userEntity->extract();
			unset($updateArray['id']);
			$query = ["update user set ", $updateArray, "where id=%i", $userEntity->getId()];
		}

		if (!$this->connection->query($query)) {
			throw new InvalidStateException("Bad");
		}
	}

	/**
	 * @param int $id
	 * @param string $newPassString
	 * @return \Dibi\Result|int
	 */
	public function changePassword($id, $newPassString) {
		$newPassHashed = Passwords::hash($newPassString);
		$query = ["update user set password = %s where id = %i", $newPassHashed, $id];
		return $this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return \Dibi\Result|int
	 */
	public function setUserActive($id) {
		$query = ["update user set active = 1 where id = %i", $id];
		return $this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return \Dibi\Result|int
	 */
	public function setUserInactive($id) {
		$query = ["update user set active = 0 where id = %i", $id];
		return $this->connection->query($query);
	}

	/**
	 * @param int $id
	 * @return \Dibi\Result|int
	 */
	public function updateLostLogin($id) {
		$query = ["update user set last_login = NOW() where id = %i", $id];
		return $this->connection->query($query);
	}
}
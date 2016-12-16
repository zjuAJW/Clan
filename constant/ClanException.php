<?php
class ClanException extends Exception{
	const CLAN_DO_NOT_EXIST = 1;
	const CLAN_IS_FULL = 2;
	const PERMISSION_DENIED = 3;
	const ILLEGAL_OPERATION = 4;
}
?>
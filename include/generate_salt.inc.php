<?php

function generate_salt()
{
	mt_srand(microtime(true)*100000 + memory_get_usage(true));
	return md5(uniqid(mt_rand(), true));
}

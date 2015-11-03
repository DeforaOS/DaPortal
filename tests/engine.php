<?php //$Id$
//Copyright (c) 2015 Pierre Pronchery <khorben@defora.org>
//This file is part of DeforaOS Web DaPortal
//
//This program is free software: you can redistribute it and/or modify
//it under the terms of the GNU General Public License as published by
//the Free Software Foundation, version 3 of the License.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.
//
//You should have received a copy of the GNU General Public License
//along with this program.  If not, see <http://www.gnu.org/licenses/>.



require_once('./tests.php');


class TestEngine extends Engine
{
	public function match()
	{
		return -1;
	}

	public function attach()
	{
	}

	public function backtraceTest($priority)
	{
		$closure = function($priority)
		{
			return $this->logBacktrace($priority);
		};
		return $closure($priority);
	}

	public function logTest($priority, $message, $expected)
	{
		$res = $this->logMessage($priority, $message);
		if($res != $expected)
		{
			print("Expected: $expected\n");
			print("Obtained: $res\n");
			return FALSE;
		}
		return TRUE;
	}
}

$ret = 0;
$engine = new TestEngine();
$engine->attach();
$ret |= ($engine->logTest('LOG_ERR', 'Test string',
		'./engine.php: Error: Test string') === TRUE) ? 0 : 2;
$ret |= ($engine->logTest('LOG_ERR', "Multi-line\ntest string",
		"./engine.php: Error: Multi-line
./engine.php: Error: test string") === TRUE)
		? 0 : 4;
$ret |= ($engine->logTest('LOG_ERR', FALSE,
		'./engine.php: Error: false') === TRUE) ? 0 : 8;
$engine->backtraceTest('LOG_NOTICE');
exit($ret);

?>

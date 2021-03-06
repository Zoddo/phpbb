<?php
/**
*
* @package testing
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once dirname(__FILE__) . '/../template/template_test_case.php';

class phpbb_pagination_pagination_test extends phpbb_template_template_test_case
{
	protected $test_path = 'tests/pagination';

	public function return_callback_implode()
	{
		return implode('-', func_get_args());
	}

	public function setUp()
	{
		parent::setUp();
		$user = $this->getMock('\phpbb\user');
		$user->expects($this->any())
			->method('lang')
			->will($this->returnCallback(array($this, 'return_callback_implode')));
		$this->pagination = new \phpbb\pagination($this->template, $user);
	}

	public function generate_template_pagination_data()
	{
		return array(
			array(
				'page.php',
				'start',
				95,
				10,
				10,
				'pagination
				:previous::page.php
				:else:1:page.php
				:current:2:page.php?start=10
				:else:3:page.php?start=20
				:else:4:page.php?start=30
				:else:5:page.php?start=40
				:ellipsis:9:page.php?start=80
				:else:10:page.php?start=90
				:next::page.php?start=20
				:u_prev:page.php
				:u_next:page.php?start=20',
			),
			array(
				'page.php',
				'start',
				95,
				10,
				20,
				'pagination
				:previous::page.php?start=10
				:else:1:page.php
				:else:2:page.php?start=10
				:current:3:page.php?start=20
				:else:4:page.php?start=30
				:else:5:page.php?start=40
				:else:6:page.php?start=50
				:ellipsis:9:page.php?start=80
				:else:10:page.php?start=90
				:next::page.php?start=30
				:u_prev:page.php?start=10
				:u_next:page.php?start=30',
			),
			array(
				'test/page/%d',
				'/page/%d',
				95,
				10,
				10,
				'pagination
				:previous::test
				:else:1:test
				:current:2:test/page/2
				:else:3:test/page/3
				:else:4:test/page/4
				:else:5:test/page/5
				:ellipsis:9:test/page/9
				:else:10:test/page/10
				:next::test/page/3
				:u_prev:test
				:u_next:test/page/3',
			),
			array(
				'test/page/%d',
				'/page/%d',
				95,
				10,
				20,
				'pagination
				:previous::test/page/2
				:else:1:test
				:else:2:test/page/2
				:current:3:test/page/3
				:else:4:test/page/4
				:else:5:test/page/5
				:else:6:test/page/6
				:ellipsis:9:test/page/9
				:else:10:test/page/10
				:next::test/page/4
				:u_prev:test/page/2
				:u_next:test/page/4',
			),
		);
	}

	/**
	* @dataProvider generate_template_pagination_data
	*/
	public function test_generate_template_pagination($base_url, $start_name, $num_items, $per_page, $start_item, $expect)
	{
		$this->pagination->generate_template_pagination($base_url, 'pagination', $start_name, $num_items, $per_page, $start_item);
		$this->template->set_filenames(array('test' => 'pagination.html'));

		$this->assertEquals(str_replace("\t", '', $expect), $this->display('test'));
	}

	public function on_page_data()
	{
		return array(
			array(
				'page.php',
				10,
				10,
				0,
				'PAGE_OF-1-1',
				'on_page
				per_page:10
				on_page:1
				base_url:page.php',
			),
		);
	}

	/**
	* @dataProvider on_page_data
	*/
	public function test_on_page($base_url, $num_items, $per_page, $start_item, $expect_return, $expect)
	{
		$this->assertEquals($expect_return, $this->pagination->on_page($base_url, $num_items, $per_page, $start_item));

		$this->template->set_filenames(array('test' => 'on_page.html'));

		$this->assertEquals(str_replace("\t", '', $expect), $this->display('test'));
	}

	public function validate_start_data()
	{
		return array(
			array(
				-1,
				0,
			),
			array(
				0,
				0,
			),
			array(
				10,
				10,
			),
			array(
				20,
				10,
			),
			array(
				30,
				10,
			),
		);
	}

	/**
	* @dataProvider validate_start_data
	*/
	public function test_validate_start($start, $expect)
	{
		$this->assertEquals($expect, $this->pagination->validate_start($start, 10, 20));
	}

	public function reverse_start_data()
	{
		return array(
			array(
				10,
				5,
				15,
				0,
			),
			array(
				10,
				10,
				25,
				5,
			),
		);
	}

	/**
	* @dataProvider reverse_start_data
	*/
	public function test_reverse_start($start, $limit, $num_items, $expect)
	{
		$this->assertEquals($expect, $this->pagination->reverse_start($start, $limit, $num_items));
	}

	public function reverse_limit_data()
	{
		return array(
			array(
				10,
				10,
				15,
				5,
			),
			array(
				20,
				10,
				15,
				1,
			),
		);
	}

	/**
	* @dataProvider reverse_limit_data
	*/
	public function test_reverse_limit($start, $per_page, $num_items, $expect)
	{
		$this->assertEquals($expect, $this->pagination->reverse_limit($start, $per_page, $num_items));
	}
}

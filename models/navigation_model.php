<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Navigation_model extends BF_Model {

	protected $table		= "navigation";
	protected $key			= "nav_id";
	protected $soft_deletes	= false;
	protected $date_format	= "datetime";
	protected $set_created	= false;
	protected $set_modified = false;

	/**
	 * Load a group
	 * 
	 * @access public
	 * @param string $abbrev The group abbrevation
	 * @return mixed
	 */
	public function load_group($nav_group_id)
	{

		$this->db->where(array('nav_group_id' => $nav_group_id, 'parent_id' => "0"));
		$group_links = $this->navigation_model->order_by('position, title')->find_all();

		$has_current_link = false;
			
		// Loop through all links and add a "current_link" property to show if it is active
		if( ! empty($group_links) )
		{
			foreach($group_links as &$link)
			{
				$full_match 	= site_url($this->uri->uri_string()) == $link->url;
				$segment1_match = site_url($this->uri->rsegment(1, '')) == $link->url;
				
				// Either the whole URI matches, or the first segment matches
				if($link->current_link = $full_match || $segment1_match)
				{
					$has_current_link = true;
				}
				
				//build a multidimensional array for submenus
				if($link->has_kids > 0 AND $link->parent_id == 0)
				{
					$link->children = $this->get_children($link->nav_id);
					
					foreach($link->children as $key => $child)
					{
						//what is this world coming to?
						if($child->has_kids > 0)
						{
							$link->children[$key]->children = $this->get_children($child->nav_id);
							
							foreach($link->children[$key]->children as $index => $item)
							{
								if($item->has_kids > 0)
								{
									$link->children[$key]->children[$index]->children = $this->get_children($item->nav_id);
								}
							}
						}
					}
				}
			}
			
		}

		// Assign it 
	    return $group_links;
	}

	/**
	 * Get children
	 *
	 * @access public
	 * @param integer Get links by parent id
	 * @return mixed
	 */
	public function get_children($id)
	{
		$children = $this->db->where('parent_id', $id)
							->order_by('position')
							->order_by('title')
							->get('navigation')
							->result();
							
		return $children;
	}

}

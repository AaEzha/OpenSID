<?php defined('BASEPATH') or exit('No direct script access allowed');

class User_groups extends MY_Controller
{

	public function __construct()
	{
		parent::__construct();
		session_start();
		$this->load->library(array('form_validation'));
		$this->load->helper(array('html', 'language'));

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
		$this->load->model(array('common_model', 'users_modal', 'users_groups'));

		                
		if (!$this->ion_auth->logged_in()) {
			redirect('auth', 'refresh');
		}

		$sess_data = $this->session->all_userdata();
	
			// pr($sess_data);
			// die(); 	
	}


       public function clear()
	{
		unset($_SESSION['cari']);
		unset($_SESSION['filter']);
		redirect('users');
	}  


	public function index($p = 1, $o = 0)
	{
		$data['p'] = $p;
		$data['o'] = $o;

		if (isset($_SESSION['cari']))
			$data['cari'] = $_SESSION['cari'];
		else $data['cari'] = '';

		if (isset($_SESSION['filter']))
			$data['filter'] = $_SESSION['filter'];
		else $data['filter'] = '';

		if (isset($_POST['per_page']))
			$_SESSION['per_page']=$_POST['per_page'];
		$data['per_page'] = $_SESSION['per_page'];

		$data['paging'] = $this->users_groups->paging($p,$o);
		$data['main'] = $this->users_groups->list_data($o, $data['paging']->offset, $data['paging']->per_page);
		$data['keyword'] = $this->users_groups->autocomplete();
		$data['list_group'] = $this->users_groups->list_group();

                // set the flash data error message if there is one
		$data['message'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('message');

		// list the groups
		$data['groups'] = $this->ion_auth->groups()->result();

		$this->session->set_flashdata('message', $this->ion_auth->messages());
		$data['page'] = 'user_groups/view_group';
		$header = $this->header_model->get_config();
                $this->load->view('header',$header);		
	        $this->load->view('dashboard',$data);
	}

        public function search()
	{
		$cari = $this->input->post('cari');
		if ($cari != '')
			$_SESSION['cari'] = $cari;
		else unset($_SESSION['cari']);
		redirect('user_groups');
	}

	public function filter()
	{
		$filter = $this->input->post('filter');
		if ($filter != 0)
			$_SESSION['filter'] = $filter;
		else unset($_SESSION['filter']);
		redirect('user_groups');
	}

       	// create a new group
	public function create_group()
	{
		
		if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin())
		{
			redirect('auth', 'refresh');
		}

		// validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('create_group_validation_name_label'), 'trim|required|alpha_dash');

		if ($this->form_validation->run() === TRUE)
		{
			$new_group_id = $this->ion_auth->create_group($this->input->post('group_name'), $this->input->post('description'));
			if ($new_group_id)
			{
				// check to see if we are creating the group
				// redirect them back to the admin page
				$this->session->set_flashdata('message', $this->ion_auth->messages());
                                $_SESSION['success'] = 1;
				redirect("user_groups", 'refresh');
			}
		}
		else
		{
			// display the create group form
			// set the flash data error message if there is one
			$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));
                        
			$this->data['group_name'] = [
				'name'  => 'group_name',
				'id'    => 'group_name',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('group_name'),
			];
			$this->data['description'] = [
				'name'  => 'description',
				'id'    => 'description',
				'type'  => 'text',
				'value' => $this->form_validation->set_value('description'),
			];

		     	$this->data['page'] = 'user_groups/create_group';
		        $header = $this->header_model->get_config();
                        $this->load->view('header',$header);		
	                $this->load->view('dashboard',$this->data);
                        
		}
	}

	// edit a group
	public function edit_group($id)
	{
			
		// bail if no group id given
		if (!$id || empty($id)) {
                        $_SESSION['success'] = -1;
			redirect('user_groups', 'refresh');
		}

		$data['title'] = $this->lang->line('edit_group_title');

		$group = $this->ion_auth->group($id)->row();

                $modul = $this->Users_modal->list_data_main();
             
                // level 1
                $privilege = $this->Users_modal->list_data_main();
                
                $currentPrivilege = $this->Users_modal->get_user_privileges($id);
                                  
                // level 2
                $privilege1 = $this->Users_modal->list_data_sub();
                $privilege1_create = $privilege1;
                $privilege1_update = $privilege1;
                $privilege1_delete = $privilege1;
                $privilege1_print = $privilege1;
                                                                        
		$currentPrivilege1 = $this->Users_modal->get_user_privileges($id);
                $currentPrivilege1_create = $this->Users_modal->get_user_privileges_create($id);  
                $currentPrivilege1_update = $this->Users_modal->get_user_privileges_update($id);
                $currentPrivilege1_delete = $this->Users_modal->get_user_privileges_delete($id);
                $currentPrivilege1_print = $this->Users_modal->get_user_privileges_print($id);
                                
                 
		// validate form input
		$this->form_validation->set_rules('group_name', $this->lang->line('edit_group_validation_name_label'), 'trim|required');

		if (isset($_POST) && !empty($_POST)) {

			if ($this->form_validation->run() === true) {

				//views
				if ($this->ion_auth->is_admin()) {
                	        //Update the groups user belongs to

                                        $privilegeData = $this->input->post('privlg');
                                         
                                        $privilegeData1 = $this->input->post('privlg5');
                                        $privilegeData1_create = $this->input->post('privlg6');
                                        $privilegeData1_update = $this->input->post('privlg7');
                                        $privilegeData1_delete = $this->input->post('privlg8');
                                        $privilegeData1_print = $this->input->post('privlg9');

					if (empty($privilegeData)) {
						$msg = "Harus ada salah satu Hak Akses yang dipilih";
						$this->session->set_flashdata('error', $msg);
                                                $_SESSION['success'] = -1;
                				$_SESSION['error_msg'] = "Harus ada salah satu Hak Akses yang dipilih";
						redirect("user_groups/edit_group/" . $id, 'refresh');
					}
                                         
                                        //LEVEL 1
                                        //lihat level1
                                        if (isset($privilegeData) && !empty($privilegeData)) {

						$query = $this->Users_modal->remove_from_privileges($privilegeData, $id);

					        foreach ($privilegeData as $key => $value) {
							$data1 = array('perm_id' => $privilegeData[$key], 'group_id' => $id, 'create_id' => NULL,'update_id' => NULL,'delete_id' => NULL,'print_id' => NULL);
							$result = $this->common_model->add('group_perm', $data1);
						}
					}
                                       
                                       //LEVEL 2
                                       //lihat level2
                                       if (isset($privilegeData1) && !empty($privilegeData1)) {

                                                foreach ($privilegeData1 as $key => $value) {
						        $data2 = array('perm_id' => $privilegeData1[$key], 'group_id' => $id, 'create_id' => $privilegeData1_create[$key],'update_id' => $privilegeData1_update[$key],'delete_id' => $privilegeData1_delete[$key],'print_id' => $privilegeData1_print[$key]);

							$perm_read = $this->common_model->add('group_perm', $data2);
						}
					}

                                       //tambah level2
                                       if (isset($privilegeData1_create) && !empty($privilegeData1_create)) {

						 foreach ($privilegeData1_create as $key => $value) {
							
                                                        $data3 = array('create_id' => $privilegeData1_create[$key]);

				                        $perm_create = $this->common_model->update($id, $data3, 'group_perm');
                                                 }
					}
				
                                        //ubah level2
                                        if (isset($privilegeData1_update) && !empty($privilegeData1_update)) {

						foreach ($privilegeData1_update as $key => $value) {
							                                                        
                                                        $data4 = array('update_id' => $privilegeData1_update[$key]);

				                        $perm_update = $this->common_model->update($id, $data4, 'group_perm');
						}
					}
                                        
                                        //hapus level2
                                        if (isset($privilegeData1_delete) && !empty($privilegeData1_delete)) {

						foreach ($privilegeData1_delete as $key => $value) {
							
                                                       $data5 = array('delete_id' => $privilegeData1_delete[$key]);

				                       $perm_delete = $this->common_model->update($id, $data5, 'group_perm');
						}
					}

                                        //cetak dan unduh level2
                                        if (isset($privilegeData1_print) && !empty($privilegeData1_print)) {

						foreach ($privilegeData1_print as $key => $value) {
						       
                                                       $data6 = array('print_id' => $privilegeData1_print[$key]);

				                       $perm_print = $this->common_model->update($id, $data6, 'group_perm');
						}
					}

                                 }

                                $data = array('name' => $this->input->post('group_name'), 'description' => $this->input->post('group_description'));

				$group_update = $this->common_model->update($id, $data, 'groups');

				if ($group_update) {
					$this->session->set_flashdata('message', $this->lang->line('edit_group_saved'));
                                        $_SESSION['success'] = 1;
					redirect("user_groups", 'refresh');
				} else {
					$this->session->set_flashdata('message', $this->ion_auth->errors());
                                        $_SESSION['success'] = -1;                                        
				}
			}
		}

		// set the flash data error message if there is one
		$data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

                // pass the user to the view
		$data['group'] = $group;
		
                $data['privileges'] = $privilege;
                $data['crtPrivilege'] = $currentPrivilege;
                
                $data['privileges1'] = $privilege1;
                $data['privileges1_create'] = $privilege1_create;
                $data['privileges1_update'] = $privilege1_update;
                $data['privileges1_delete'] = $privilege1_delete;
                $data['privileges1_print'] = $privilege1_print;
                $data['crtPrivilege1'] = $currentPrivilege1;
                $data['crtPrivilege1_create'] = $currentPrivilege1_create;
                $data['crtPrivilege1_update'] = $currentPrivilege1_update;
                $data['crtPrivilege1_delete'] = $currentPrivilege1_delete;
                $data['crtPrivilege1_print'] = $currentPrivilege1_print;


		$readonly = $this->config->item('admin_group', 'ion_auth') === $group->name ? 'readonly' : '';

		$data['group_name'] = array(
			'name' => 'group_name',
			'id' => 'group_name',
			'type' => 'text',
			'class' => 'form-control',
			'value' => $this->form_validation->set_value('group_name', $group->name),
			$readonly => $readonly,
		);
		$data['group_description'] = array(
			'name' => 'group_description',
			'id' => 'group_description',
			'class' => 'form-control',
			'type' => 'text',
			'value' => $this->form_validation->set_value('group_description', $group->description),
			$readonly => $readonly,
		);


		$data['page'] = 'user_groups/edit_group';
		$header = $this->header_model->get_config();
                $this->load->view('header',$header);		
	        $this->load->view('dashboard',$data);
	}


        public function _render_page($view, $data = null, $returnhtml = false)//I think this makes more sense
	{

		$this->viewdata = (empty($data)) ? $this->data : $data;

		$view_html = $this->load->view($view, $this->viewdata, $returnhtml);

		if ($returnhtml) return $view_html;//This will return html on 3rd argument being true
	}

	public function delete_group($group_id = '')

	{
		$this->ion_auth->delete_group($group_id);
                redirect('User_groups', 'refresh');
	}


	public function delete_group_all()

	{              
                $this->ion_auth->delete_group_all();
                redirect('User_groups', 'refresh');
		
	}


       	/*
	 * Add New Permissions
	 */
	public function permissions($value = '')
	{
		if (!$this->ion_auth->is_admin()) {
			return show_error("You Must Be An Administrator To View This Page");
		}

		if ($_POST) {
			$perm = post('perm');

			$data = array('perm_name' => $perm);

			$result = $this->common_model->add('permissions', $data);

			if ($result) {
				$msg = "Permission Added Successfully";
				$this->session->set_flashdata('success', $msg);
				redirect('user_groups/permissions', 'refresh');
			} else {
				$msg = "Error";
				$this->session->set_flashdata('error', $msg);
				redirect('user_groups/permissions', 'refresh');
			}
		} else {

			$data['perm'] = $this->common_model->select('permissions');

			$data['page'] = 'user_groups/permissions';
			$this->load->view('dashboard',$data);
			// $this->_render_page("dashboard", $data);

		}

	}

	/*
	 * Delete Permission
	 */
	public function delete_perm($id)
	{
		if (!$this->ion_auth->is_admin()) {
			return show_error("You Must Be An Administrator To View This Page");
		}

		$del_id = array('perm_id' => $id);

		$result = $this->common_model->delete($del_id, "permissions");

		if ($result) {
			$msg = "Permission Delete Successfully";
			$this->session->set_flashdata('success', $msg);
			redirect('user_groups/permissions', 'refresh');
		} else {
			$msg = "Error";
			$this->session->set_flashdata('error', $msg);
			redirect('user_groups/permissions', 'refresh');
		}
	}

	//Check Duplicate group name
	public function check_group_name()
	{
		$group_name = $this->input->post('group_name');

		$result = $this->Users_groups->check_group('groups', $group_name);

		if ($result) {
			echo "ok::";
		}
	}

    /*
	 * Get permission for update
	 */
	public function get_perm($id)
	{
		$edit_id = array('perm_id' => $id);

		$result = $this->common_model->update_data($edit_id, 'permissions');

		echo json_encode($result);
	}

    /*
	 * Update Permission
	 */
	public function update_perm()
	{
		if (!$this->ion_auth->is_admin()) {
			return show_error("You Must Be An Administrator To View This Page");
		}
		$perm = post('perm');
		$id = post('edit');

		$data = array('perm_name' => $perm);
		$edit_id = array('perm_id' => $id);

		$result = $this->Users_groups->update($edit_id, $data, "permissions");

		if ($result) {
			$msg = "Permission Update Successfully";
			$this->session->set_flashdata('success', $msg);
			redirect('user_groups/permissions', 'refresh');
		} else {
			$msg = "Error";
			$this->session->set_flashdata('error', $msg);
			redirect('user_groups/permissions', 'refresh');
		}
	}
}

/* End of file User_groups.php */
/* Location: ./application/controllers/User_groups.php */


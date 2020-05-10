<?php class Statistik_penduduk_model extends Laporan_penduduk_model {

/* Gunakan model ini untuk mulai refactor statistik penduduk
 * Mungkin bisa gunakan anonymous classes yg disediakan di PHP 7.x
 * Usahakan supaya di Laporan_penduduk_model juga menggunakan query builder Codeigniter
*/

	public function __construct()
	{
		parent::__construct();
		$this->load->model('program_bantuan_model');
	}

	private function order_sql($o)
	{
		//Ordering SQL
		switch ($o)
		{
			case 1: $this->db->order_by('u.id'); break;
			case 2: $this->db->order_by('u.id DESC'); break;
			case 3: $this->db->order_by('laki'); break;
			case 4: $this->db->order_by('laki DESC'); break;
			case 5: $this->db->order_by('jumlah'); break;
			case 6: $this->db->order_by('jumlah DESC'); break;
			case 7: $this->db->order_by('perempuan'); break;
			case 8: $this->db->order_by('perempuan DESC'); break;
		}
	}

	private function jml_per_kategori()
	{
		// Ambil data sasaean penduduk
		$this->db->select('u.id, u.nama')
			->select('COUNT(pp.id) AS jumlah')
		  ->select('COUNT(CASE WHEN p.sex = 1 THEN pp.id END) AS laki')
		  ->select('COUNT(CASE WHEN p.sex = 2 THEN pp.id END) AS perempuan')
			->from('program u')
			->join('program_peserta pp', 'pp.program_id = u.id', 'left')
			->join('tweb_penduduk p', 'pp.peserta = p.nik', 'left')
			->where('u.sasaran', '1')
			->group_by('u.id');
		$this->$order_sql;
		$penduduk = $this->db->get_compiled_select();

		// Ambil data sasaean keluarga
		// $this->db->select('u.id, u.nama')
		// 	->select('COUNT(pp.id) AS jumlah')
		//   ->select('COUNT(CASE WHEN p.sex = 1 THEN pp.id END) AS laki')
		//   ->select('COUNT(CASE WHEN p.sex = 2 THEN pp.id END) AS perempuan')
		// 	->from('program u')
		// 	->join('program_peserta pp', 'pp.program_id = u.id', 'left')
		// 	->join('tweb_keluarga k', 'pp.peserta = k.no_kk', 'left')
		// 	->join('tweb_penduduk p', 'k.nik_kepala = p.id', 'left')
		// 	->where('u.sasaran', '2')
		// 	->group_by('u.id');
		// $this->$order_sql;
		// $kepala_keluarga = $this->db->get_compiled_select();

		// $data = $this->db->query($penduduk . ' UNION ' . $kepala_keluarga)
		// 	->result_array();

		$data = $this->db->query($penduduk)->result_array();
		return $data;
	}

	public function list_data($o)
	{
		$data = $this->jml_per_kategori($o);

		$semua = $this->data_jml_semua_penduduk();
		$semua = $this->persentase_semua($semua);

		$total = $this->hitung_total($data);
		$data[] = $this->baris_jumlah($total, 'PENERIMA');
		$data[] = $this->baris_belum($semua, $total, 'BUKAN PENERIMA');
		$this->hitung_persentase($data, $semua);

		return $data;
	}

}

?>

<?php
class ModelExtensionMstoreAddress extends Model {
	public function findOrAddAddress($customer_id, $data) {
		$condition = "";
		$condition = $condition."customer_id = '" . (int)$this->customer->getId() . "'";
		$condition = $condition." AND firstname = '" . $this->db->escape($data['firstname']) . "'";
		$condition = $condition." AND lastname = '" . $this->db->escape($data['lastname']) . "'";
		$condition = $condition." AND company = '" . $this->db->escape($data['company']) . "'";
		$condition = $condition." AND address_1 = '" . $this->db->escape($data['address_1']) . "'";
		$condition = $condition." AND address_2 = '" . $this->db->escape($data['address_2']) . "'";
		$condition = $condition." AND postcode = '" . $this->db->escape($data['postcode']) . "'";
		$condition = $condition." AND city = '" . $this->db->escape($data['city']) . "'";
		$condition = $condition." AND zone_id = '" . (int)$data['zone_id'] . "'";
		$condition = $condition." AND country_id = '" . (int)$data['country_id'] . "'";
		$condition = $condition." AND custom_field = '" . $this->db->escape(isset($data['custom_field']['address']) ? json_encode($data['custom_field']['address']) : '') . "'";

		$address_query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "address WHERE ".$condition);
		if ($address_query->num_rows) {
			return (int)$address_query->row['address_id'];
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', company = '" . $this->db->escape($data['company']) . "', address_1 = '" . $this->db->escape($data['address_1']) . "', address_2 = '" . $this->db->escape($data['address_2']) . "', postcode = '" . $this->db->escape($data['postcode']) . "', city = '" . $this->db->escape($data['city']) . "', zone_id = '" . (int)$data['zone_id'] . "', country_id = '" . (int)$data['country_id'] . "', custom_field = '" . $this->db->escape(isset($data['custom_field']['address']) ? json_encode($data['custom_field']['address']) : '') . "'");

			$address_id = $this->db->getLastId();

			if (!empty($data['default'])) {
				$this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
			}

			return $address_id;
		}
	}
}

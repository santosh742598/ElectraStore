<?php
class ModelExtensionMStoreCoupon extends Model {
	public function getCoupons() {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1'");
		return $query->rows;
	}
}

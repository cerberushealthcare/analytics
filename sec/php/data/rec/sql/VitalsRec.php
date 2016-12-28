<?php
class VitalsRec extends SqlRec {
	//
	public $dataVitalsId;
	public $userGroupId;
	public $clientId;
	public $sessionId;
	public $date;
	public $pulse;
	public $resp;
	public $bpSystolic;
	public $bpDiastolic;
	public $bpLoc;
	public $temp;
	public $tempRoute;
	public $wt;
	public $wtUnits;
	public $height;
	public $hc;
	public $hcUnits;
	public $wc;
	public $wcUnits;
	public $o2Sat;
	public $o2SatOn;
	public $bmi;
	public $htUnits;
	public $dateUpdated;
	public $active;
	public $wtLbs;
	public $htIn;
	public $updatedBy;
	
	

	static $PROPS_TO_QUID = array(
			'pulse'        => 'vitals.pulse',
			'resp'         => 'vitals.rr',
			'bpSystolic'   => 'vitals.sbp',
			'bpDiastolic'  => 'vitals.dbp',
			'bpLoc'        => 'vitals.loc',
			'temp'         => 'vitals.temp',
			'tempRoute'    => 'vitals.tempRoute',
			'wt'           => 'vitals.#Weight',
			'wtUnits'      => 'vitals.lbsKgs',
			'height'       => 'vitals.#Height',
			'htUnits'      => 'vitals.unitsHt',
			'hc'           => 'vitals.#hc',
			'hcUnits'      => 'vitals.inCm',
			'wc'           => 'vitals.#wc',
			'wcUnits'      => 'vitals.inCmWC',
			'o2Sat'        => 'vitals.#O2Sat',
			'o2SatOn'      => 'vitals.O2SatOn',
			'bmi'          => 'vitals.bmi');
	//
	public function create($ugid, $cid, $date, $pulse, $wt) {
		$me = new static();
		$me->userGroupId = $ugid;
		$me->clientId = $cid;
		//$me->agent = $agent; //this may break things....
		if ($me->clientId)
			$me = $me->fetchDupe(); /*look for existing record after assigning enough fields to identify*/
			$me->date = $date;
			$me->pulse = $pulse;
			$me->wt = $wt;
			return $me;
	}
	
	public function fetchDupe() {
		$c = new static($this);
		//$c->sessionId = CriteriaValue::isNull();
		$c->pulse = $this->pulse;
		$c->date = $this->date;
		try {
			$dupe = static::fetchOneBy($c);
		}
		catch (Exception $e) {
			Logger::debug('ERROR in VitalsRec fetchDupe: ' . substr($e->getMessage(), 0, 200));
		}
		Logger::debug('VitalsRec::fetchDupe: Returning a ' . gettype($dupe));
		return $dupe;
	}
	
	public function getSqlTable() {
		return 'data_vitals';
	}
	public function getEncryptedFids() {
		return array('date');
	}
	public function getKey() {
		return 'date';
	}
	public function getFaceClass() {
		return 'FaceVital';
	}
	public function getJsonFilters() {
		return array(
				'date' => JsonFilter::editableDateTime(),
				'dateUpdated' => JsonFilter::informalDateTime());
	}
	
	public function save() {
		parent::save();
		Hdata_VitalsDate::from($this)->save();
	}
	public function toJsonObject(&$o) {
		if (! $this->isAuditSnapshot()) {
			$o->all = $this->getAllValues();
			$o->htcm = $this->getHtCm();
			$o->wtkg = $this->getWtKg();
			$o->bp = $this->getBp();
			$o->o2 = $this->getO2();
		}
	}
	public function getAuditRecName() {
		return 'Vital';
	}
	public function validate(&$rv) {
		//$rv->isTodayOrPast('date');
	}
	public function getDateOnly() {
		return substr($this->date, 0, 10);
	}
	/*
	 * @return FaceVital
	 */
	public function asFace($replaceFace = null, $allowEmptyRecord = false) {
		logit_r($replaceFace, 'asFace');
		$face = parent::asFace($replaceFace);
		if ($replaceFace)
			$face->date = $replaceFace->date;
			logit_r($face, 'face');
			$face->bpSystolic = str_replace('/', '', $face->bpSystolic);
			$face->toNumeric(
					'pulse',
					'bpSystolic',
					'bpDiastolic',
					'temp',
					'wt',
					'height',
					'hc',
					'wc',
					'o2Sat',
					'bmi');
			if ($face->temp)
				$face->temp = number_format($face->temp, 1);
				if ($face->bpSystolic == null && $face->bpDiastolic == null)
					$face->bpLoc = null;
					if ($face->wt)
						$face->wtLbs = $face->getWtLb();
						if ($face->height)
							$face->htIn = $face->getHtIn();
							if ($face->hc)
								$face->hcIn = $face->getHcIn();
								if (! $allowEmptyRecord)
									if (! $face->isAnySet())
										$face = null;
										return $face;
	}
	//
	public function getBp() {
		return "$this->bpSystolic/$this->bpDiastolic $this->bpLoc";
	}
	public function getO2() {
		$s = $this->o2Sat;
		if ($s && $this->o2SatOn)
			$s = "$s - $this->o2SatOn";
			return $s;
	}
	public function getWtKg() {
		if (self::formatWtUnits($this->wtUnits) == 'KG')
			return $this->wt;
			else
				return round($this->wt * 0.45359, 2);
	}
	public function getWtLb() {
		if (self::formatWtUnits($this->wtUnits) == 'KG')
			return round($this->wt * 2.20462, 2);
			else
				return $this->wt;
	}
	public function getHtCm() {
		if (self::formatHtUnits($this->htUnits) == 'CM')
			return $this->height;
			else
				return round($this->height * 2.54, 2);
	}
	public function getHtIn() {
		if (self::formatHtUnits($this->htUnits) == 'CM')
			return round($this->height * 0.3937, 2);
			else
				return $this->height;
	}
	public function getHcCm() {
		if (self::formatHtUnits($this->hcUnits) == 'CM')
			return $this->hc;
			else
				return round($this->hc * 2.54, 2);
	}
	public function getHcIn() {
		if (self::formatHtUnits($this->hcUnits) == 'CM')
			return round($this->hc * 0.3937, 2);
			else
				return $this->hc;
	}
	public function getAllValues() {
		$a = array();
		if ($this->pulse)
			$a[] = 'Pulse: ' . $this->pulse;
			if ($this->resp)
				$a[] = 'Resp: ' . $this->resp;
				if ($this->bpSystolic)
					$a[] = 'BP: ' . $this->getBp();
					if ($this->temp)
						$a[] = 'Temp: ' . $this->temp;
						if ($this->wt)
							$a[] = 'Wt: ' . $this->wt;
							if ($this->height)
								$a[] = 'Height: ' . $this->height;
								if ($this->hc)
									$a[] = 'HC: ' . $this->hc;
									if ($this->wc)
										$a[] = 'WC: ' . $this->wc;
										if ($this->o2Sat)
											$a[] = 'O2: ' . trim($this->o2Sat . ' ' . $this->o2SatOn);
											if ($this->bmi)
												$a[] = 'BMI: ' . $this->bmi;
												return $a;
	}
	public function getAllFriendlyValues() {
		$a = array();
		if ($this->pulse)
			$a[] = 'Pulse: ' . $this->pulse . ' Beats Per Minute';
			if ($this->resp)
				$a[] = 'Respiratory Rate: ' . $this->resp . ' Breaths Per Minute';
				if ($this->bpSystolic)
					$a[] = 'Blood Pressure: ' . "$this->bpSystolic (Systolic) / $this->bpDiastolic (Diastolic)";
					if ($this->temp)
						$a[] = 'Temperature: ' . $this->temp . ' F';
						if ($this->wt)
							$a[] = 'Weight: ' . "$this->wt $this->wtUnits";
							if ($this->height)
								$a[] = 'Height: ' . "$this->height $this->htUnits";
								if ($this->hc)
									$a[] = 'Head Circumference: ' . "$this->hc $this->hcUnits";
									if ($this->wc)
										$a[] = 'Waist Circumference: ' . "$this->wc $this->wcUnits";
										if ($this->bmi)
											$a[] = 'BMI: ' . $this->bmi;
											if ($this->o2Sat)
												$a[] = 'Oxygen: ' . trim($this->o2Sat . ' ' . $this->o2SatOn);
												return $a;
	}
	protected function isBpSet() {
		if ($this->bpDiastolic && $this->bpSystolic)
			return true;
	}
	protected function isHtWtSet() {
		if ($this->height && $this->wt)
			return true;
	}
	private function isAnySet() {
		return
		$this->pulse ||
		$this->resp ||
		$this->bpSystolic ||
		$this->temp ||
		$this->wt ||
		$this->height ||
		$this->hc ||
		$this->wc ||
		$this->o2Sat;
	}
	//
	protected static function formatWtUnits($u) {
		return (strtoupper(substr($u, 0, 1)) == 'K') ? 'KG' : 'LB';
	}
	protected static function formatHtUnits($u) {
		return (strtoupper(substr($u, 0, 1)) == 'C') ? 'CM' : 'IN';
	}
	/**
	 * @param object $o JSON
	 * @param int $ugid
	 * @return Vital
	 */
	static function fromUi($o, $ugid, $userId) {
		$rec = new Vital($o);
		$rec->userGroupId = $ugid;
		$rec->updatedBy = $userId;
		return $rec;
	}
	/**
	 * Build new face records from session history
	 * @param int cid
	 */
	static function buildFacesFromSessions($cid) {
		$sessions = SessionVital::fetchAllUnbuilt($cid);
		if ($sessions) {
			$faces = FaceVital::fetchMap($cid);
			parent::_buildFacesFromSessions($sessions, $faces);
		}
	}
	/**
	 * Build new face records from old (active=null)
	 * @param int $cid
	 */
	static function buildFacesFromOldFaces($cid) {
		$c = FaceVital::asCriteria($cid);
		$c->active = CriteriaValue::isNull();
		$oldFaces = parent::fetchAllBy($c);
		foreach ($oldFaces as $oldFace) {
			$face = $oldFace->asFace();
			if ($face)
				$face->save();
		}
	}
}
?>
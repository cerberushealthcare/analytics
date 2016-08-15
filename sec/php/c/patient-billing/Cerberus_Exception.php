<?php
//
class CerberusException extends Exception {}
class CerberusLoginExpired extends CerberusException {}
class CerberusPracticeNotFound extends CerberusException {}
class CerberusPatientNotFound extends CerberusException {}
class CerberusUserNotFound extends CerberusException {}
class CerberusEncounterNotFound extends CerberusException {}
class CerberusEncounterAlreadyExists extends CerberusException {}
class CerberusErrorResponse extends CerberusException {}
class CerberusBadResponse extends CerberusException {}

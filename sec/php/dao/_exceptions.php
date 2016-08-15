<?php

// Common exceptions
class SecurityException extends Exception {}
class ParseException extends Exception {}
class SqlException extends Exception {}
class DuplicateInsertException extends SqlException {}
class AddUserException extends Exception {}
class ChargeException extends Exception {}
class InvalidDataException extends Exception {}
class PasswordChangeException extends Exception {}

// Survey exceptions
// class MissingRequiredException extends Exception {}
// class PastEndException extends Exception {}

<?php
interface Custom_Interface_Validatator
{
  function valid(array $fields);
  function isValid($fieldName = null);
}
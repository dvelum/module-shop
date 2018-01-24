<?php
class Dvelum_Shop_Product_Field_StringTest extends PHPUnit_Framework_TestCase
{
    public function testSet()
    {
        $field = new Dvelum_Shop_Product_Field_String([
           'name' => 'code',
            "required"=>false,
            "external_code"=>"84",
            "group"=>"4",
            "multivalue"=>true
        ]);

        $value = 4;

        $value = $field->filter($value);
        $this->assertTrue($field->isValid($value));

        $value = ['4','re','tr'];
        $value = $field->filter($value);
        $this->assertTrue($field->isValid($value));

        $value = 100;
        $this->assertFalse($field->isValid($value));


        $field = new Dvelum_Shop_Product_Field_String([
            'name' => 'code',
            "required"=>false,
            "external_code"=>"84",
            "group"=>"4",
            "multivalue"=>false
        ]);

        $value = 4;
        $value = $field->filter($value);
        $this->assertTrue($field->isValid($value));

        $value = ['4','re','tr'];
        $value = $field->filter($value);
        $this->assertTrue($field->isValid($value));

        $value = ['4','re','tr'];
        $this->assertFalse($field->isValid($value));

        $value = 100;
        $this->assertFalse($field->isValid($value));
    }
}
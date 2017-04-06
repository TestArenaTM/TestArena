<?php
abstract class Custom_Model_Dictionary_Category_Abstract extends Custom_Model_Standard_Abstract
{
  /* for atributes which not belong to any category*/
  const CATEGORY_0 = 0;
  
  /*Main categories*/
  const CATEGORY_1 = 1;
  const CATEGORY_2 = 2;
  const CATEGORY_3 = 3;
  const CATEGORY_4 = 4;
  const CATEGORY_5 = 5;
  const CATEGORY_6 = 6;
  const CATEGORY_7 = 11;
  const CATEGORY_8 = 12;
  const CATEGORY_9 = 13;
  const CATEGORY_10 = 14;
  const CATEGORY_11 = 15;
  const CATEGORY_12 = 18;
  const CATEGORY_13 = 25;
  
  /*Subcategories */
  const CATEGORY_6_1 = 7;
  const CATEGORY_6_2 = 8;
  const CATEGORY_6_3 = 9;
  const CATEGORY_6_4 = 10;
  const CATEGORY_11_1 = 16;
  const CATEGORY_11_2 = 17;
  const CATEGORY_12_1 = 19;
  const CATEGORY_12_2 = 20;
  const CATEGORY_12_3 = 21;
  
  /*Sub subcategories*/
  const CATEGORY_12_3_1 = 22;
  const CATEGORY_12_3_2 = 23;
  const CATEGORY_12_3_3 = 24;
  
  static protected $_categoriesByLvl = array(
    '0' => array(
      self::CATEGORY_0 => array(
        'name' => 'Atrybuty stałe',
        'parent' => 0
      )
    ),
    '1' => array(
      self::CATEGORY_1 => array(
        'name' => 'CMS',
        'parent' => 0
      ),
      self::CATEGORY_2 => array(
        'name' => 'CRM',
        'parent' => 0
      ),
      self::CATEGORY_3 => array(
        'name' => 'e-learning',
        'parent' => 0
      ),
      self::CATEGORY_4 => array(
        'name' => 'HRM',
        'parent' => 0
      ),
      self::CATEGORY_5 => array(
        'name' => 'BI & controlling',
        'parent' => 0
      ),
      self::CATEGORY_6 => array(
        'name' => 'Bezpieczeństwo',
        'parent' => 0
      ),
      self::CATEGORY_7 => array(
        'name' => 'Zarządzanie projektami',
        'parent' => 0
      ),
      self::CATEGORY_8 => array(
        'name' => 'Finanse i księgowość',
        'parent' => 0
      ),
      self::CATEGORY_9 => array(
        'name' => 'SCM',
        'parent' => 0
      ),
      self::CATEGORY_10 => array(
        'name' => 'ERP',
        'parent' => 0
      ),
      self::CATEGORY_11 => array(
        'name' => 'Biurowe',
        'parent' => 0
      ),
      self::CATEGORY_12 => array(
        'name' => 'Tworzenie oprogramowania',
        'parent' => 0
      ),
      self::CATEGORY_13 => array(
        'name' => 'Intranet',
        'parent' => 0
      )
    ),
    '2' => array(
      self::CATEGORY_6_1 => array(
        'name' => 'Backup & Restore',
        'parent' => self::CATEGORY_6
      ),
      self::CATEGORY_6_2 => array(
        'name' => 'Odzyskiwanie danych',
        'parent' => self::CATEGORY_6
      ),
      self::CATEGORY_6_3 => array(
        'name' => 'Szyfrowanie danych',
        'parent' => self::CATEGORY_6
      ),
      self::CATEGORY_6_4 => array(
        'name' => 'Oprogramowanie antywirusowe',
        'parent' => self::CATEGORY_6
      ),
      self::CATEGORY_11_1 => array(
        'name' => 'OCR',
        'parent' => self::CATEGORY_11
      ),
      self::CATEGORY_11_2 => array(
        'name' => 'Klient e-mail',
        'parent' => self::CATEGORY_11
      ),
      self::CATEGORY_12_1 => array(
        'name' => 'Specyfikowanie i projektowanie',
        'parent' => self::CATEGORY_12
      ),
      self::CATEGORY_12_2 => array(
        'name' => 'Środowiska programistyczne',
        'parent' => self::CATEGORY_12
      ),
      self::CATEGORY_12_3 => array(
        'name' => 'Zarządzanie i kontrola jakości',
        'parent' => self::CATEGORY_12
      )
    ),
    '3' => array(
      self::CATEGORY_12_3_1 => array(
        'name' => 'Automatyzacja testowania',
        'parent' => self::CATEGORY_12_3
      ),
      self::CATEGORY_12_3_2 => array(
        'name' => 'Zarządzanie przypadkami testowymi',
        'parent' => self::CATEGORY_12_3
      ),
      self::CATEGORY_12_3_3 => array(
        'name' => 'BugTracker(DTT)',
        'parent' => self::CATEGORY_12_3
      )
    )
  );
  
  static protected $_categoryTree = array(
    0 => array(
      'id' => 0,
      'name' => '',
      'level' => 0,
      'children' => array(
        self::CATEGORY_1 => array(
          'id' => self::CATEGORY_1,
          'name' => 'CMS',
          'level' => 1,
          'children' => array()
        ),
	self::CATEGORY_2 => array(
          'id' => self::CATEGORY_2,
          'name' => 'CRM',
          'level' => 1,
          'children' => array()
        ),
	self::CATEGORY_3 => array(
          'id' => self::CATEGORY_3,
          'name' => 'e-learning',
          'level' => 1,
          'children' => array()
        ),
        self::CATEGORY_4 => array(
          'id' => self::CATEGORY_4,
          'name' => 'HRM',
          'level' => 1,
          'children' => array()
        ),
        self::CATEGORY_5 => array(
          'id' => self::CATEGORY_5,
          'name' => 'BI & controlling',
          'level' => 1,
          'children' => array()
        ),
        self::CATEGORY_6 => array(
          'id' => self::CATEGORY_6,
          'name' => 'Bezpieczeństwo',
          'level' => 1,
          'children' => array(
            self::CATEGORY_6_1 => array(
              'id' => self::CATEGORY_6_1,
              'name' => 'Backup & Restore',
              'level' => 2,
              'children' => array()
            ),
            self::CATEGORY_6_2 => array(
              'id' => self::CATEGORY_6_2,
              'name' => 'Odzyskiwanie danych',
              'level' => 2,
              'children' => array()
            ),
            self::CATEGORY_6_3 => array(
              'id' => self::CATEGORY_6_3,
              'name' => 'Szyfrowanie danych',
              'level' => 2,
              'children' => array()
            ),
            self::CATEGORY_6_4 => array(
              'id' => self::CATEGORY_6_4,
              'name' => 'Oprogramowanie antywirusowe',
              'level' => 2,
              'children' => array()
            )  
          )
        ),
        self::CATEGORY_7 => array(
          'id' => self::CATEGORY_7,
          'name' => 'Zarządzanie projektami',
          'level' => 1,
          'children' => array()
        ),
        self::CATEGORY_8 => array(
          'id' => self::CATEGORY_8,
          'name' => 'Finanse i księgowość',
          'level' => 1,
          'children' => array()
        ),
        self::CATEGORY_9 => array(
          'id' => self::CATEGORY_9,
          'name' => 'SCM',
          'level' => 1,
          'children' => array()
        ),
        self::CATEGORY_10 => array(
          'id' => self::CATEGORY_10,
          'name' => 'ERP',
          'level' => 1,
          'children' => array()
        ),
        self::CATEGORY_11 => array(
          'id' => self::CATEGORY_11,
          'name' => 'Biurowe',
          'level' => 1,
          'children' => array(
            self::CATEGORY_11_1 => array(
              'id' => self::CATEGORY_11_1,
              'name' => 'OCR',
              'level' => 2,
              'children' => array()
            ),
            self::CATEGORY_11_2 => array(
              'id' => self::CATEGORY_11_2,
              'name' => 'Klient e-mail',
              'level' => 2,
              'children' => array()
            )
          )
        ),
        self::CATEGORY_12 => array(
          'id' => self::CATEGORY_12,
          'name' => 'Tworzenie oprogramowania',
          'level' => 1,
          'children' => array(
            self::CATEGORY_12_1 => array(
              'id' => self::CATEGORY_12_1,
              'name' => 'Specyfikowanie i projektowanie',
              'level' => 2,
              'children' => array()
            ),
            self::CATEGORY_12_2 => array(
              'id' => self::CATEGORY_12_2,
              'name' => 'Środowiska programistyczne',
              'level' => 2,
              'children' => array()
            ),
            self::CATEGORY_12_3 => array(
              'id' => self::CATEGORY_12_3,
              'name' => 'Zarządzanie i kontrola jakości',
              'level' => 2,
              'children' => array(
                self::CATEGORY_12_3_1 => array(
                  'id' => self::CATEGORY_12_3_1,
                  'name' => 'Automatyzacja testowania',
                  'level' => 3,
                  'children' => array()
                ),
                self::CATEGORY_12_3_2 => array(
                  'id' => self::CATEGORY_12_3_2,
                  'name' => 'Zarządzanie przypadkami testowymi',
                  'level' => 3,
                  'children' => array()
                ),
                self::CATEGORY_12_3_3 => array(
                  'id' => self::CATEGORY_12_3_3,
                  'name' => 'BugTracker(DTT)',
                  'level' => 3,
                  'children' => array()
                )
              )
            )
          )
        ),
        self::CATEGORY_13 => array(
          'id' => self::CATEGORY_13,
          'name' => 'Intranet',
          'level' => 1,
          'children' => array()
        )
      )
    )
  );
  
  static public function getMaxCategoryLvl()
  {
    return count(self::$_categoriesByLvl);
  }
  
  static public function getCategoriesByLvl($level = 1)
  {
    if (!array_key_exists($level, self::$_categoriesByLvl))
    {
      throw new Exception('Category level '.$level.' not exists');
    }
    return self::$_categoriesByLvl[$level];
  }
  
  static public function getCategoryObjectsByLvl($level = 1)
  {
    $categories = self::getCategoriesByLvl($level);
    
    $result = array();
    
    if (count($categories) > 0)
    {
      foreach ($categories as $id => $category)
      {
        $category = new Application_Model_Category(array('id' => $id, 'name' => $category['name']));
        $result[$id] = $category;
      }
    }
    
    return $result;
  }
  
  static public function getCategoryTree()
  {
    return self::$_categoryTree;
  }
  
  static public function getCategoryChildrenByParentIds($parentIds)
  {
    $data = array();
    
    if (!is_array($parentIds))
    {
      $parentIds = explode(',', $parentIds);
    }
    
    if (count($parentIds) > 0)
    {
      foreach ($parentIds as $id)
      {
        $category = self::_getChildren($id, self::$_categoryTree);
        
        if (count($category['children']) > 0)
        {
          $data[$id]['id'] = $category['id'];
          $data[$id]['name'] = $category['name'];
          $data[$id]['level'] = $category['level'];

          foreach ($category['children'] as $children)
          {
            $data[$id]['children'][] = array(
              'id'       => $children['id'],
              'name'     => $children['name'],
              'level'    => $children['level'],
              'selected' => false
            );
          }
        }
      }
    }
    
    return $data;
  }
  
  static public function getCategoryWithChildrenByParentIds($parentIds)
  {
    $data = array();
    
    if (!is_array($parentIds))
    {
      $parentIds = explode(',', $parentIds);
    }
    
    if (count($parentIds) > 0)
    {
      foreach ($parentIds as $id)
      {
        $category = self::_getChildren($id, self::$_categoryTree);
        
        $data[$id]['id'] = $category['id'];
        $data[$id]['name'] = $category['name'];
        $data[$id]['level'] = $category['level'];
        $data[$id]['children'] = array();

        if (count($category['children']) > 0)
        {
          foreach ($category['children'] as $children)
          {
            $data[$id]['children'][] = array(
              'id'       => $children['id'],
              'name'     => $children['name'],
              'selected' => false
            );
          }
        }
      }
    }
    
    return $data;
  }
  
  static public function getCategoryChildrenTreeByIds($categoryIds)
  {
    $data = array();
    
    if (!is_array($categoryIds))
    {
      $categoryIds = explode(',', $categoryIds);
    }
    
    if (count($categoryIds) > 0)
    {
      foreach ($categoryIds as $id)
      {
        $data[$id] = self::_getChildren($id, self::$_categoryTree);
      }
    }
    
    return $data;
  }
  
  static protected function _getChildren($categoryId, array $categoryTree)
  {
    if (!array_key_exists($categoryId, $categoryTree))
    {
      foreach($categoryTree as $category)
      {
        $result = self::_getChildren($categoryId, $category['children']);
          
        if (null !== $result)
        {
          return $result;
        }
      }
      
      return null;
    }
    else
    {
      return $categoryTree[$categoryId];
    }
  }
  
  static public function getIdsByName($phrase)
  {
    $keys = array();
    $categories = array();
    
    for ($i = 0; $i < count(self::$_categoriesByLvl); $i++)
    {
      $categories = $categories + self::$_categoriesByLvl[$i];
    }
    
    foreach ($categories as $id => $category)
    {
      if (stripos($category['name'], $phrase) === 0)
      {
        $keys[] = $id;
      }
    }
    
    return $keys;
  }
  
  static public function getNameById($id)
  {
    $categories = array();
    
    for ($i = 0; $i < count(self::$_categoriesByLvl); $i++)
    {
      $categories = $categories + self::$_categoriesByLvl[$i];
    }
    
    if (array_key_exists($id, $categories))
    {
      return $categories[$id]['name'];
    }
    
    return null;
  }
}
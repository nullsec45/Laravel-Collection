<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Data\Person;

class CollectionTest extends TestCase
{
    public function testCreateCollection(){
        $collection=collect([1,2,3]);
        $this->assertEqualsCanonicalizing([1,2,3], $collection->all());
    }

    public function testForEach(){
        $collection=collect([1,2,3,4,5,6,7,8,9,10]);
        foreach($collection as $key => $value){
            self::assertEquals($key +1, $value);
        }
    }

    public function testCrud(){
        $collection=collect([]);
        $collection->push(1,2,3);
        $this->assertEqualsCanonicalizing([1,2,3], $collection->all());

        $result=$collection->pop();
        $this->assertEquals(3, $result);
        $this->assertEqualsCanonicalizing([1,2], $collection->all());
    }

    public function testMap(){
        $collection=collect([1,2,3]);
        $result=$collection->map(function($item){
            return $item *2;
        });
        $this->assertEqualsCanonicalizing([2,4,6], $result->all());
    }

    public function testMapInto(){
        $collection=collect(["Fajar"]);
        $result=$collection->mapInto(Person::class);
        $this->assertEquals([new Person("Fajar")], $result->all());
    }

    public function testMapSpread(){
        $collection=collect([["Rama","Fajar"],["Akflakhatul","Azmi"]]);

        $result=$collection->mapSpread(function ($firstName, $lastName){
            $fullName=$firstName." ".$lastName;
            return new Person($fullName);
        });

        $this->assertEquals([
            new Person("Rama Fajar"),
            new Person("Akflakhatul Azmi")
        ], $result->all());
    }

    public function testMapToGroup(){
        $collection=collect([
            [
                "name" => "Rama",
                "department" => "IT"
            ],
            [
                "name" => "Fajar",
                "department" => "IT"
            ],
            [
                "name" => "Entong",
                "department" => "HR"
            ]
        ]);

        $result=$collection->mapToGroups(function($person){
            return [
                $person["department"] => $person["name"]
            ];
        });
        $this->assertEquals(["IT" => collect(["Rama","Fajar"]),"HR" => collect(["Entong"])], $result->all());
    }

    public function testZip(){
        $collection1=collect([1,2,3]);
        $collection2=collect([4,5,6]);
        $collection3=$collection1->zip($collection2);

        $this->assertEquals([
            collect([1,4]),
            collect([2,5]),
            collect([3,6])
        ], $collection3->all());
    }

    public function testConcat(){
        $collection1=collect([1,2,3]);
        $collection2=collect([4,5,6]);
        $collection3=$collection1->concat($collection2);

        $this->assertEquals([1,2,3,4,5,6], $collection3->all());
    }

    public function testCombine(){
        $collection1=["name", "country"];
        $collection2=["Fajar","Indonesia"];
        $collection3=collect($collection1)->combine($collection2);

        $this->assertEqualsCanonicalizing([
            "name" => "Fajar",
            "country" => "Indonesia"
        ], $collection3->all());
    }

    public function testCollapse(){
        $collection=collect([
            [1,2,3],
            [4,5,6],
            [7,8,9]
        ]);
        $result=$collection->collapse();
        $this->assertEqualsCanonicalizing([1,2,3,4,5,6,7,8,9], $result->all());
    }

    public function testFlatMap(){
        $collection=collect([[
            "name" => "Rama",
            "hobbies" => ["Coding","Reading"]
        ]]);
        $result=$collection->flatMap(function($item){
            $hobbies=$item["hobbies"];
            return $hobbies;
        });
        $this->assertEqualsCanonicalizing(["Coding","Reading"], $result->all());
    }

    public function testJoin(){
        $collection=collect(["Rama","Fajar","Fadhillah"]);

       $this->assertEquals("Rama_Fajar_Fadhillah", $collection->join("_"));
        $this->assertEquals("Rama_Fajar-Fadhillah", $collection->join("_","-"));
    }

    public function testFilter(){
        $collection=collect([
            "Rama" => 100,
            "Fajar" => 90,
            "Fadhillah" => 70
        ]);

        $result=$collection->filter(function($value, $key){
            return $value >= 90;
        });

        $this->assertEquals([
            "Rama" => 100,
            "Fajar" => 90,
        ], $result->all());
    }

    public function testFilterIndex(){
        $collection=collect([1,2,3,4,5,6,7,8,9,10]);

        // jika menggunakan filter index, maka index yang ke filter akan dibuang
        $result=$collection->filter(function($value, $key){
                return $value % 2 == 0;
        });

        $this->assertEqualsCanonicalizing([2,4,6,8,10],$result->all());
    }

    public function testPartition(){
        $collection=collect([
            "Rama" => 100,
            "Fajar" => 90,
            "Fadhillah" => 80
        ]);

        [$result1, $result2]=$collection->partition(function($item, $Key){
            return $item >= 90;
        });

        $this->assertEquals(["Rama" => 100,"Fajar" => 90], $result1->all());
        $this->assertEquals(["Fadhillah" => 80], $result2->all());
    }

    public function testTesting(){
        $collection=collect(["Rama","Fajar","Fadhillah"]);
        $this->assertTrue($collection->contains("Rama"));
        $this->assertTrue($collection->contains(function($value,$key){
                return $value="Fajar";
        }));
    }

    public function testGrouping(){
        $collection=collect([
            ["name" => "Rama","department" => "IT"],
            ["name" => "Fajar","department" => "IT"],
            ["name" => "Fadhillah", "department" => "HR"]
        ]);

        $result=$collection->groupBy("department");

        $this->assertEquals([
            "IT" => collect([
                    ["name" => "Rama","department" => "IT"],
                    ["name" => "Fajar","department" => "IT"]
                 ]),
            "HR" => collect([
                    ["name" => "Fadhillah","department" => "HR"]
                 ])
        ], $result->all());

        $resultFunction=$collection->groupBy(function($value, $key){
            return $value["department"];
        });

        $this->assertEquals([
            "IT" => collect([
                    ["name" => "Rama","department" => "IT"],
                    ["name" => "Fajar","department" => "IT"]
                 ]),
            "HR" => collect([
                    ["name" => "Fadhillah","department" => "HR"]
                 ])
        ], $resultFunction->all());
    }

    public function testSlicing(){
        $collection=collect([1,2,3,4,5,6,7,8,9,10]);

        $result=$collection->slice(3);

        $this->assertEqualsCanonicalizing([4,5,6,7,8,9,10], $result->all());

        $result2=$collection->slice(3, 2);
        $this->assertEqualsCanonicalizing([4,5], $result2->all());
    }
}

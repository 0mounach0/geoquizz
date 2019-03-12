<?php
namespace gq\controllers;

use gq\models\Serie;
use gq\models\Photo;

/** 
 * Classe PhotoController
 */
class PhotoController extends Controller {

    /**
     * Creation Photo
     * @param $req
     * @param $resp
     * @param $args
     * @return mixed|void
     */

    public function createPhoto($req, $resp, $args){

        try{

            //------
            $jsonData = $req->getParsedBody();

            if (!isset($jsonData['desc'])) return $resp->withStatus(400);
            if (!isset($jsonData['lat'])) return $resp->withStatus(400);
            if (!isset($jsonData['lng'])) return $resp->withStatus(400); 
            if (!isset($jsonData['url'])) return $resp->withStatus(400); 


            $photo = new Photo();
            $photo->id = Uuid::uuid4();
            $photo->desc = filter_var($jsonData['desc'], FILTER_SANITIZE_SPECIAL_CHARS);
            $photo->lat = filter_var($jsonData['lat'], FILTER_SANITIZE_SPECIAL_CHARS);
            $photo->lng = filter_var($jsonData['lng'], FILTER_SANITIZE_SPECIAL_CHARS);
            $photo->url = $photo->id + '.jpg';

            // Create photo
            if($photo->save()) {

                $data = [
                    'type' => 'resource',
                    'meta' => ['date' =>date('d-m-Y')],
                    'photo' => $photo->toArray()
                ];

                $serie = Serie::where('id','=',$args['id'])->firstOrFail();

                $photo->serie()->attach($serie);

                return $this->jsonOutup($resp, 201, $data);

            }else {

                $data = ['type' => 'resource',
                'meta' => ['date' =>date('d-m-Y')],
                'message' => 'photo Not Created'
                ];

                return $this->jsonOutup($resp, 400, $data);
            
        }
    
        }catch(\Exception $e){


        }
    }

    /**
     * Serie
     * Toutes les series
     * @param $req
     * @param $resp
     * @param $args
     */
    public function getSeries($req, $resp, $args){

        try{

            $series = Serie::select()->get();
            $total = $series->count();
            $data = [
                'type' => 'collection',
                'date' =>date('d-m-Y'),
                'count' => $total,
                'series' => $series->toArray()
            ];

            return $this->jsonOutup($resp, 200, $data);
            
        }catch(\Exception $e){


        }
    }


    /**
     * Les series par ID
     * @param $req
     * @param $resp
     * @param $args
     */
    public function getSerie($req, $resp, $args){

        try{

            $serie = Serie::where('id','=',$args['id'])->firstOrFail();
            
         
                $data = [
                    'type' => 'resource',
                    'date' => date('d-m-Y'),
                    'serie' => $serie->toArray(),
                    "links"=> [
                        "series"=> [ "href" => "/series/".$args['id']."/photos/" ],
                        "self" => [ "href" => "/series/".$args['id']."/" ]
                    ]
            ];
            

            return $this->jsonOutup($resp, 200, $data);


        }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){

            $data = [
                'type' => 'error',
                'error' => 404,
                'message' => 'ressource non disponible : /series/ '. $args['id']
            ];

            return $this->jsonOutup($resp, 404, $data);


        }

    }


}



?>
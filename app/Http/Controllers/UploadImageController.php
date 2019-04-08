<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Image_uploaded;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;
//use thiagoalessio\TesseractOCR\TesseractOCR;
use GoogleCloudVision\GoogleCloudVision;
use GoogleCloudVision\Request\AnnotateImageRequest;
use Deft\MrzParser\MrzParser;

class UploadImageController extends Controller
{
    public $path;
    public $dimensions;

    public function __construct()
    {
        //DEFINISIKAN PATH
        $this->path = storage_path('app/public/images');
        //DEFINISIKAN DIMENSI
        $this->dimensions = ['245', '300', '500'];
    }

    public function upload(Request $request)
    {
        $this->validate($request, [
            'image' => 'required|image|mimes:jpg,png,jpeg'
        ]);

        //JIKA FOLDERNYA BELUM ADA
        if (!File::isDirectory($this->path)) {
            //MAKA FOLDER TERSEBUT AKAN DIBUAT
            File::makeDirectory($this->path);
        }

        //MENGAMBIL FILE IMAGE DARI FORM
        $file = $request->file('image');
        //MEMBUAT NAME FILE DARI GABUNGAN TIMESTAMP DAN UNIQID()
        $fileName = Carbon::now()->timestamp . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        //UPLOAD ORIGINAN FILE (BELUM DIUBAH DIMENSINYA)
        Image::make($file)->save($this->path . '/' . $fileName);

        //LOOPING ARRAY DIMENSI YANG DI-INGINKAN
        //YANG TELAH DIDEFINISIKAN PADA CONSTRUCTOR
        foreach ($this->dimensions as $row) {
            //MEMBUAT CANVAS IMAGE SEBESAR DIMENSI YANG ADA DI DALAM ARRAY
            $canvas = Image::canvas($row, $row);
            //RESIZE IMAGE SESUAI DIMENSI YANG ADA DIDALAM ARRAY
            //DENGAN MEMPERTAHANKAN RATIO
            $resizeImage  = Image::make($file)->resize($row, $row, function($constraint) {
                $constraint->aspectRatio();
            });

            //CEK JIKA FOLDERNYA BELUM ADA
            if (!File::isDirectory($this->path . '/' . $row)) {
                //MAKA BUAT FOLDER DENGAN NAMA DIMENSI
                File::makeDirectory($this->path . '/' . $row);
            }

            //MEMASUKAN IMAGE YANG TELAH DIRESIZE KE DALAM CANVAS
            $canvas->insert($resizeImage, 'center');
            //SIMPAN IMAGE KE DALAM MASING-MASING FOLDER (DIMENSI)
            $canvas->save($this->path . '/' . $row . '/' . $fileName);
        }

        //SIMPAN DATA IMAGE YANG TELAH DI-UPLOAD
        Image_uploaded::create([
            'name' => $fileName,
            'dimensions' => implode('|', $this->dimensions),
            'path' => $this->path,

        ]);
        /*$ocr = new TesseractOCR();
        $ocr->image($this->path.'/'.$fileName);
        $ocr->availableLanguages("ind");
        $result = $ocr->run();*/

        $image = base64_encode(file_get_contents($request->file('image')));


        $ocr = new AnnotateImageRequest();
        $ocr->setImage($image);
        $ocr->setFeature("DOCUMENT_TEXT_DETECTION");

        $ocrRequest = new GoogleCloudVision([$ocr], env('GOOGLE_CLOUD_KEY'));

        $response = $ocrRequest->annotate();

        $result = $response->responses[0]->fullTextAnnotation->text;

        $res = explode( "\n", $result );

        $off = count($res);

        $res1 = $res[$off-3];
        $res2 = $res[$off-2];

        //dd($res2);
       $zmrscan = $res1.$res2;

       $parser = new MrzParser();
       $traveldocument = $parser->parseString($zmrscan);

       $typedocument = $traveldocument->getType();
       $namaakhir = $traveldocument->getPrimaryIdentifier();
       $namaawal = $traveldocument->getSecondaryIdentifier();
       $gender = $traveldocument->getSex();
       $tgllahir=Carbon::parse($traveldocument->getDateOfBirth(), 'UTC');
       $datelahir = $tgllahir->isoFormat('DD MMMM YYYY');
       //$tgllahir = Carbon::createFromFormat('d/m/Y g:i:s A',$traveldocument->getDateOfBirth(),null);
       $tglexpire = Carbon::parse($traveldocument->getDateOfExpiry(),'UTC');
       $dateexpire = $tglexpire->isoFormat('DD MMMM YYYY');
       $nopassport = $traveldocument->getDocumentNumber();
       $negaraissue = $traveldocument->getIssuingCountry();
       $warganegara = $traveldocument->getNationality();
       $idnumber = $traveldocument->getPersonalNumber();

       //$resultscan = json_encode($traveldocument);
       $zmrcode = $res1." \r\n".$res2;

       //$lahir = Carbon::parse($tgllahir,'UTC');
       //print $lahir->isoFormat('DD MMMM YYYY');
       return redirect()->back()->with('success', $zmrcode)
                                ->with('tipedokumen', $typedocument)
                                ->with('nopassport', $nopassport)
                                ->with('negaraissued', $negaraissue)
                                ->with('tglexpire', $dateexpire)
                                ->with('namaawal', $namaawal)
                                ->with('namabelakang', $namaakhir)
                                ->with('noktp', $idnumber)
                                ->with('gender', $gender)
                                ->with('tgllahir', $datelahir)
                                ->with('warganegara', $warganegara);
    }
}

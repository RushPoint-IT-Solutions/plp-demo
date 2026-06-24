<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTemplatesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('document_templates')) {
            Schema::create('document_templates', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('slug')->unique();
                $table->json('content_json')->nullable();
                $table->timestamps();
            });
        }

        $now = now();
        DB::table('document_templates')->updateOrInsert(
            ['slug' => 'honorable-dismissal'],
            [
                'name' => 'Honorable Dismissal',
                'content_json' => json_encode($this->defaultHonorableDismissalTemplate()),
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down()
    {
        Schema::dropIfExists('document_templates');
    }

    private function defaultHonorableDismissalTemplate()
    {
        return json_decode(<<<'JSON'
{"page":{"width_mm":210,"height_mm":297,"orientation":"portrait","background":"#ffffff"},"elements":[{"id":"seal","type":"text","text":"PLP\nSEAL","top":3.6,"left":16.2,"width":8.5,"font_family":"Arial","font_size":8,"font_weight":"bold","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.05},{"id":"header_city","type":"text","text":"City Government of Pasig","top":3.2,"left":28,"width":44,"font_family":"Times New Roman","font_size":8,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.1},{"id":"header_school","type":"text","text":"PAMANTASAN NG LUNGSOD NG PASIG","top":4.45,"left":24,"width":52,"font_family":"Times New Roman","font_size":10,"font_weight":"bold","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.1},{"id":"header_office","type":"text","text":"OFFICE OF THE UNIVERSITY REGISTRAR","top":5.95,"left":24,"width":52,"font_family":"Times New Roman","font_size":9,"font_weight":"bold","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.1},{"id":"header_address","type":"text","text":"Alkalde Jose St., Kapasigan, Pasig City, Philippines","top":7.25,"left":24,"width":52,"font_family":"Arial","font_size":6.5,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.1},{"id":"certificate_bar","type":"text","text":"CERTIFICATE OF ELIGIBILITY TO TRANSFER / HONORABLE DISMISSAL","top":10.4,"left":12,"width":76,"font_family":"Arial","font_size":7.5,"font_weight":"bold","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.1},{"id":"hd_no","type":"text","text":"HD NO:\n{{hd_no}}","top":10.2,"left":77,"width":15,"font_family":"Arial","font_size":7,"font_weight":"bold","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.1},{"id":"date_issued","type":"text","text":"DATE:\n{{date_issued}}","top":13.3,"left":77,"width":17,"font_family":"Arial","font_size":7,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.1},{"id":"to_registrar","type":"text","text":"TO WHOM IT MAY CONCERN:","top":16.7,"left":10,"width":38,"font_family":"Arial","font_size":7.5,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.2},{"id":"certify_body","type":"text","text":"This is to certify that {{student_name}}\nis eligible for admission to transfer from this university.\nLast school year/semester attended: ________________________________","top":21,"left":14,"width":72,"font_family":"Arial","font_size":7.6,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.45},{"id":"registrar_signature_line","type":"text","text":"__________________________________________\nUniversity Registrar","top":29.7,"left":58,"width":30,"font_family":"Arial","font_size":7,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.25},{"id":"cut_line","type":"text","text":"- - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -","top":34.7,"left":9,"width":82,"font_family":"Arial","font_size":8,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1},{"id":"transcript_title","type":"text","text":"REQUEST FOR OFFICIAL TRANSCRIPT OF RECORDS","top":37.5,"left":28,"width":44,"font_family":"Arial","font_size":8,"font_weight":"bold","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.1},{"id":"receipt_hd","type":"text","text":"HD NO:\n{{hd_no}}\nDate:\n{{date_issued}}","top":39,"left":77,"width":17,"font_family":"Arial","font_size":7,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.15},{"id":"registrar_request","type":"text","text":"THE REGISTRAR\nPamantasan ng Lungsod ng Pasig\nPasig City","top":43.2,"left":10,"width":32,"font_family":"Arial","font_size":7,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.3},{"id":"admission_line","type":"text","text":"Sir/Madam:\n\nYour Professionalism:\n\nPlease issue an Official Transcript of Records of the student,\nwhose credentials appear below:","top":47.6,"left":10,"width":45,"font_family":"Arial","font_size":7.2,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.35},{"id":"purpose_line","type":"text","text":"Diploma non-issuance was not a Portal of School Official","top":59.8,"left":58,"width":34,"font_family":"Arial","font_size":6.7,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"center","line_height":1.1},{"id":"student_fields","type":"text","text":"Student's Signature: ________________________________________________\nStudent Number:      {{student_no}}\nProgram:             {{course_program}}\nStudent's Name:      {{student_name}}\nBilling Address:     ________________________________________________\nSchool Contact No.:  ________________________________________________","top":66,"left":10,"width":78,"font_family":"Arial","font_size":7.4,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.7},{"id":"copy_options","type":"text","text":"(   )   PLM\n(   )   Direct to School","top":87.6,"left":10,"width":25,"font_family":"Arial","font_size":7,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"left","line_height":1.5},{"id":"footer_note","type":"text","text":"Not valid without University Seal","top":91.2,"left":70,"width":22,"font_family":"Arial","font_size":6.6,"font_weight":"normal","font_style":"normal","text_decoration":"none","text_align":"right","line_height":1.1}]}
JSON
        , true);
    }
}

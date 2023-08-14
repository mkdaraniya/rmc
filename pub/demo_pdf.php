<?php

echo "script start";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '5G');
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;

require '../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

global $objectManager;
$objectManager = $bootstrap->getObjectManager();
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
$fileFactory = $objectManager->create('Magento\Framework\App\Response\Http\FileFactory');
$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');


$fileSystem = $objectManager->create('Magento\Framework\Filesystem');
$varDirectory = $fileSystem->getDirectoryWrite(Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
$customDirectoryName = 'invoice_pdf';

if (!$varDirectory->isExist($customDirectoryName)) {
    $varDirectory->create($customDirectoryName);
}

$fileName = '000000135.pdf';
$filePath = $varDirectory2->getAbsolutePath($fileName);

$fileContents = '';
if ($varDirectory2->isExist($fileName)) {
    $fileContents = base64_encode(file_get_contents($filePath));
}

$curl = curl_init();

$postParam = [
    "Method" => "UploadPaperlessDocumentation",
    "Params" => [
        "TransactionID" => "000000135",
        "Name" => "Mark",
        "Country" => "US",
        "Document" => $fileContents,
        "DocumentType" => 5
    ]
];

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://www.shippypro.com/api/',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($postParam),
    CURLOPT_HTTPHEADER => array(
        'Authorization: Basic NTExN2Q5MzNhODlmNWQ1ZjY4YzRiNjgxZDA3YWM3MmE6',
        'Content-Type: application/json',
    ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;


die;

$orderId = 81;
$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
$shipping = $order->getShippingAddress()->getData();


echo "<pre>";
foreach ($order->getAllVisibleItems() as $_item) {

    $product = $productRepository->getById($_item->getProductId());

    $deliveryOptionText = '';
    if ($product->getData('delivery_form') != '') {
        $optionId = $product->getData('delivery_form');
        $attribute = $product->getResource()->getAttribute('delivery_form');
        if ($attribute->usesSource()) {
            $deliveryOptionText = $attribute->getSource()->getOptionText($optionId);
        }
    }

    if ($deliveryOptionText == 'lyophilized') {

        $totalQty = $_item->getQtyOrdered();

        $qtyOptionText = '';
        if ($product->getData('quantities_available') != '') {
            $optionId = $product->getData('quantities_available');
            $attribute = $product->getResource()->getAttribute('quantities_available');
            if ($attribute->usesSource()) {
                $qtyOptionText = $attribute->getSource()->getOptionText($optionId);
            }
        }

        $originOptionText = '';
        if ($product->getData('origin') != '') {
            $optionId = $product->getData('origin');
            $attribute = $product->getResource()->getAttribute('origin');
            if ($attribute->usesSource()) {
                $originOptionText = $attribute->getSource()->getOptionText($optionId);
            }
        }

        $data = [
            'created_at' => $order->getData('created_at'),
            'qty' => $totalQty,
            'qty_available' => $qtyOptionText,
            'origin' => $originOptionText,
        ];

        $this->generatePDF($data);
    }
}


function generatePDF($data)
{
    $bootstrap = Bootstrap::create(BP, $_SERVER);
    $objectManager = $bootstrap->getObjectManager();
    $fileFactory = $objectManager->create('Magento\Framework\App\Response\Http\FileFactory');


    // First page
    $pdf = new \Zend_Pdf();
    $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
    $page = $pdf->pages[0]; // this will get reference to the first page.
    $style = new \Zend_Pdf_Style();
    $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, 13);
    $page->setStyle($style);
    $width = $page->getWidth();
    $hight = $page->getHeight();
    $x = 30;
    $pageTopalign = 850;
    $y = 850 - 100;

    $fontNormalSize = 13;
    $fontBoldSize = 13;

    $style->setFont($font, 14);
    $page->setStyle($style);

    $page->setFont(\Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_HELVETICA), 7);

    // set logo
    $imageData = new \Zend_Pdf_Resource_Image_Png('media/logo/stores/1/logoimage.png');
    $page->drawImage($imageData, 30, 740, 210, $y + 60);

    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("Pestalozzistrasse 16"), $x + 400, $y + 50, 'UTF-8');
    $style->setFont($font, 11);
    $page->setStyle($style);
    $page->drawText(__("CH-3400 Burgdorf"), $x + 400, $y + 33, 'UTF-8');
    $page->drawText(__("Switzerland"), $x + 400, $y + 16, 'UTF-8');
    $page->drawText(__("www.swant.com"), $x + 400, $y, 'UTF-8');
    $page->drawText(__("info@swant.com"), $x + 400, $y - 16, 'UTF-8');

    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, 14);
    $page->setStyle($style);
    $page->drawText(__("Toxic Substance Control Act (TSCA) Certification /"), $x + 100, 700, 'UTF-8');
    $page->drawText(__("Detailed Manufacture's Declaration "), $x + 150, 680, 'UTF-8');

    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);

    $page->drawText(__("Date: " . date("F jS, Y", strtotime($data['created_at']))), $x, 650, 'UTF-8');
    $page->drawText(__("Negative Certification"), $x, 620, 'UTF-8');

    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("I certify that all chemicals in this shipment are not subject to TSCA."), $x, 605, 'UTF-8');

    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("Company name: "), $x, 580, 'UTF-8');

    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("SWANT AG"), $x + 100, 580, 'UTF-8');

    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("Company address: "), $x + 250, 580, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("Pestalozzistrasse 16,"), $x + 360, 580, 'UTF-8');
    $page->drawText(__("CH-3400 Burgdorf, Switzerland"), $x + 250, 565, 'UTF-8');
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("Certifier name: "), $x, 540, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("Kalpana Ekam"), $x + 90, 540, 'UTF-8');
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("Certifier title: "), $x + 250, 540, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("Inside Sales"), $x + 330, 540, 'UTF-8');
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("Certifier phone number: "), $x, 500, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("+ 41 79 781 0411"), $x + 140, 500, 'UTF-8');
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("Certifier email address:  "), $x + 250, 500, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("info@swant.com"), $x + 390, 500, 'UTF-8');

    $page->drawText(__("Certifier signature: Kalpana Ekam "), $x, 470, 'UTF-8');


    $page->drawRectangle(30, 380, 570, 440, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("PRODUCT DESCRIPTION"), $x + 10, 425, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("•   Lyophilized product (2 x 200µl), stored in two transparent polypropylene tube(s)"), $x + 30, 410, 'UTF-8');
    $page->drawText(__("(Eppendorf) with screw caps."), $x + 40, 395, 'UTF-8');

    $page->drawRectangle(30, 360, 570, 380, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("The product is non-hazardous and non-infectious"), $x + 50, 365, 'UTF-8');

    $page->drawRectangle(30, 290, 570, 360, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("ORIGIN"), $x + 10, 345, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("•   Affinity Purified antibody mouse Cat. No. 300 Calbindin D28k"), $x + 40, 330, 'UTF-8');
    $page->drawText(__("•   The samples do not contain genes or expressed antigens of livestock or poultry"), $x + 40, 315, 'UTF-8');
    $page->drawText(__("    disease agents."), $x + 45, 300, 'UTF-8');

    $page->drawRectangle(30, 140, 570, 290, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("MATERIAL"), $x + 10, 275, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("•   Antibodies directed against the calcium binding protein Calbindin."), $x + 40, 260, 'UTF-8');
    $page->drawText(__("•   The material is non-toxic and non-pathogenic for life stocks and avian species."), $x + 40, 245, 'UTF-8');
    $page->drawText(__("•   Confirming the material was derived only from laboratory mammals that have not "), $x + 40, 230, 'UTF-8');
    $page->drawText(__("    been inoculated with or exposed to any livestock or poultry disease agents exotic to"), $x + 45, 215, 'UTF-8');
    $page->drawText(__("    the United states."), $x + 45, 200, 'UTF-8');
    $page->drawText(__("•   Confirming the material was derived only from laboratory mammals that did not "), $x + 40, 185, 'UTF-8');
    $page->drawText(__("    originate from a facility in which work with exotic disease agents affecting livestock "), $x + 45, 170, 'UTF-8');
    $page->drawText(__("    or avian species is conducted."), $x + 45, 155, 'UTF-8');

    $page->drawRectangle(30, 50, 570, 140, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("UTILISATION"), $x + 10, 125, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("This antibody is used for basic biomedical research at Universities, particularly for brain "), $x + 10, 110, 'UTF-8');
    $page->drawText(__("and cancer research."), $x + 10, 95, 'UTF-8');
    $page->drawText(__("The sample will be used for in vitro studies only."), $x + 10, 80, 'UTF-8');
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("For basic biomedical research (non-therapeutic/non-diagnostic at this stage)"), $x + 10, 65, 'UTF-8');

    // second page
    $pdf->pages[] = $pdf->newPage(\Zend_Pdf_Page::SIZE_A4);
    $page2 = $pdf->pages[1];

    $style = new \Zend_Pdf_Style();
    $style->setLineColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, 13);
    $page2->setStyle($style);
    $width = $page2->getWidth();
    $hight = $page2->getHeight();
    $x = 30;
    $pageTopalign = 850;
    $y = 850 - 100;

    $style->setFont($font, $fontBoldSize);
    $page2->setStyle($style);

    $page2->drawRectangle(30, 800, 570, 750, \Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);
    $style->setFont($font, $fontBoldSize);
    $page2->setStyle($style);
    $page2->drawText(__("PRODUCER"), $x + 10, 785, 'UTF-8');

    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page2->setStyle($style);
    $page2->drawText(__("•  Swant AG., Pestalozzistrasse 16, CH-3400 Burgdorf, Switzerland"), $x + 40, 770, 'UTF-8');


    $fileName = 'demo_order.pdf';

    $fileFactory->create(
        $fileName,
        $pdf->render(),
        \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR, // this pdf will be saved in var directory with the name meetanshi.pdf
        'application/pdf'
    );
}

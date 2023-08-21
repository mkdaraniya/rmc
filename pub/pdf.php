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

$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
$store = $storeManager->getStore();
$baseUrl = $store->getBaseUrl();


$fileSystem = $objectManager->create('Magento\Framework\Filesystem');

$fileName = '000000135.pdf';

$curl = curl_init();


$orderId = 165;
$order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
$invoiceService = $objectManager->create('Magento\Sales\Model\Service\InvoiceService');
$transaction = $objectManager->create('Magento\Framework\DB\Transaction');

$shipping = $order->getShippingAddress()->getData();

$invoiceId = '';
if(count($order->getInvoiceCollection())){
    foreach($order->getInvoiceCollection() as $invoice){
        $invoiceId = $invoice->getData('entity_id');
    }
}

if ($order->canInvoice()) {
echo "not invoice";

$invoice = $invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
           
/*            $transactionSave = 
                $transaction
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());
            $transactionSave->save();
*/
dd($invoice->getData('entity_id'));die;
}else{
echo "inovice";
}
die;

if($invoiceId != ''){

$invoice = $objectManager->create(
                \Magento\Sales\Api\InvoiceRepositoryInterface::class
            )->get($invoiceId);

$pdf = $objectManager->create(\Scrumwheel\Shippy\Model\Order\Pdf\Invoice::class)->getPdf([$invoice]);
                $date = $objectManager->get(
                    \Magento\Framework\Stdlib\DateTime\DateTime::class
                )->date('Y-m-d_H-i-s');
                $fileContent = ['type' => 'string', 'value' => $pdf->render()];
 
                /*$fileFactory->create(
                    'invoice' . $date . '.pdf',
                    $fileContent,
                    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                    'application/pdf'
                );*/

                $varDirectory = $fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                $fileName = 'invoice'.$date . '.pdf';
                $filePath = $varDirectory->getAbsolutePath($fileName);
		echo $fileName;
                $fileContents = '';
                if ($varDirectory->isExist($fileName)) {
                    $fileContents = base64_encode(file_get_contents($filePath));
                }
echo $fileContents;
die;

}
die;


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

        generatePDF($data);
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
    $page->drawText(__("SWANT (Swiss antibodies) AG"), $x + 350, $y + 50, 'UTF-8');
    $style->setFont($font, 11);
    $page->setStyle($style);
    $page->drawText(__("Pestalozzistrasse 16"), $x + 350, $y + 33, 'UTF-8');
    $page->drawText(__("3400 Burgdorf"), $x + 350, $y + 16, 'UTF-8');
    $page->drawText(__("Switzerland"), $x + 350, $y, 'UTF-8');

    $page->drawText(__("Phone		+41 79 781 0411"), $x + 350, $y - 16, 'UTF-8');
    $page->drawText(__("E-mail		info@swant.com"),$x + 350, $y - 26, 'UTF-8');
    $page->drawText(__("Website         www.swant.com"),$x + 350, $y - 36, 'UTF-8');
    $page->drawText(__("VAT             CHE-237.941.949"),$x + 350, $y - 46, 'UTF-8');


    $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_TIMES_BOLD);

    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);


    $page->drawText(__("Shipper / Exporter"), $x, 650, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("SWANT (Swiss antibodies) AG"), $x, 635, 'UTF-8');
    $page->drawText(__("Pestalozzistrasse 16"), $x, 620, 'UTF-8');
    $page->drawText(__("3400 Burgdorf"), $x, 605, 'UTF-8');
    $page->drawText(__("Switzerland"), $x, 590, 'UTF-8');


    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("Recipient"), $x + 300, 650, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("HHMI/UCSF / Mission Bay"), $x + 300, 635, 'UTF-8');
    $page->drawText(__("Deliver to Adeline Yong"), $x + 300, 620, 'UTF-8');
    $page->drawText(__("Ship To Jan, Yuh Nung"), $x + 300, 605, 'UTF-8');
    $page->drawText(__("1550 4TH Street"), $x + 300, 590, 'UTF-8');
    $page->drawText(__("Room 490D"), $x + 300, 575, 'UTF-8');
    $page->drawText(__("CA 94158 San Francisco"), $x + 300, 560, 'UTF-8');
    $page->drawText(__("United States of America"), $x + 300, 545, 'UTF-8');


    $style->setFont($font, $fontBoldSize);
    $page->setStyle($style);
    $page->drawText(__("Commercial Invoice"), $x, 515, 'UTF-8');
    $font = \Zend_Pdf_Font::fontWithName(\Zend_Pdf_Font::FONT_TIMES);
    $style->setFont($font, $fontNormalSize);
    $page->setStyle($style);
    $page->drawText(__("Date of Export		20.06.2023"), $x, 485, 'UTF-8');
    $page->drawText(__("P.O			HHPO1594025"), $x, 470, 'UTF-8');
    $page->drawText(__("Incoterms		FCA, Burgdorf"), $x, 455, 'UTF-8');

    $page->drawText(__("Burgdorf, 14.06.2023"), $x + 300, 515, 'UTF-8');
    $page->drawText(__("Country of export	Switzerland"), $x + 300, 485, 'UTF-8');
    $page->drawText(__("Country of manufacture	Switzerland"), $x + 300, 470, 'UTF-8');
    $page->drawText(__("Final destination	United States of A..."), $x + 300, 455, 'UTF-8');




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


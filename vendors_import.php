use App\Models\Vendor;

$vendors = [

[
'name'=>'Toyota Genuine Parts Pakistan',
'vendor_type'=>'parts_vendor',
'phone'=>'03001110001',
'email'=>'parts@toyota.pk',
'address'=>'Main Boulevard',
'city'=>'Lahore',
'ntn_number'=>'1234567-1',
'bank_name'=>'HBL',
'account_number'=>'001122334455',
'opening_balance'=>0,
'notes'=>'Toyota OEM Parts',
'is_active'=>1,
],

[
'name'=>'Honda Atlas Parts',
'vendor_type'=>'parts_vendor',
'phone'=>'03001110002',
'email'=>'parts@honda.pk',
'address'=>'Johar Town',
'city'=>'Lahore',
'ntn_number'=>'1234567-2',
'bank_name'=>'Meezan Bank',
'account_number'=>'001122334456',
'opening_balance'=>0,
'notes'=>'Honda Genuine Parts',
'is_active'=>1,
],

[
'name'=>'Pak Wheels Auction',
'vendor_type'=>'auction_house',
'phone'=>'03001110003',
'email'=>'auction@pakwheels.com',
'address'=>'Model Town',
'city'=>'Lahore',
'ntn_number'=>'1234567-3',
'bank_name'=>'UBL',
'account_number'=>'001122334457',
'opening_balance'=>0,
'notes'=>'Vehicle Auctions',
'is_active'=>1,
],

[
'name'=>'Japan Auto Imports',
'vendor_type'=>'importer',
'phone'=>'03001110004',
'email'=>'info@japanauto.pk',
'address'=>'Port Area',
'city'=>'Karachi',
'ntn_number'=>'1234567-4',
'bank_name'=>'Bank Alfalah',
'account_number'=>'001122334458',
'opening_balance'=>0,
'notes'=>'Imported Vehicles',
'is_active'=>1,
],

[
'name'=>'City Car Transport',
'vendor_type'=>'transport',
'phone'=>'03001110005',
'email'=>'transport@citycar.pk',
'address'=>'Ring Road',
'city'=>'Lahore',
'ntn_number'=>'1234567-5',
'bank_name'=>'HBL',
'account_number'=>'001122334459',
'opening_balance'=>0,
'notes'=>'Vehicle Transportation',
'is_active'=>1,
],

[
'name'=>'EFU Insurance',
'vendor_type'=>'insurance',
'phone'=>'03001110006',
'email'=>'corporate@efu.com',
'address'=>'Gulberg',
'city'=>'Lahore',
'ntn_number'=>'1234567-6',
'bank_name'=>'Meezan Bank',
'account_number'=>'001122334460',
'opening_balance'=>0,
'notes'=>'Insurance Provider',
'is_active'=>1,
],

[
'name'=>'Punjab Excise Office',
'vendor_type'=>'government',
'phone'=>'0421111111',
'email'=>'',
'address'=>'Egerton Road',
'city'=>'Lahore',
'ntn_number'=>'',
'bank_name'=>'',
'account_number'=>'',
'opening_balance'=>0,
'notes'=>'Registration',
'is_active'=>1,
],

];

foreach($vendors as $vendor){

Vendor::firstOrCreate(

[
'tenant_id'=>1,
'name'=>$vendor['name']
],

array_merge(
['tenant_id'=>1],
$vendor
)

);

}

echo "Vendor import completed.\n";

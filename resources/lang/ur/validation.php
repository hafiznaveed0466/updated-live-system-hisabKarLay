<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute کو قبول کرنا ضروری ہے۔',
    'active_url' => 'یہ :attribute درست URL نہیں ہے۔',
    'after' => ':attribute:date کے بعد کی تاریخ ہونی چاہیے۔',
    'after_or_equal' => ':attribute:date کے بعد یا اس کے مساوی تاریخ ہونی چاہیے۔',
    'alpha' => ':attribute میں صرف حروف ہو سکتے ہیں۔',
    'alpha_dash' => ':attribute میں صرف حروف, اعداد, ڈیش اور انڈر سکور ہو سکتے ہیں۔',
    'alpha_num' => ':attribute میں صرف حروف اور اعداد ہو سکتے ہیں۔',
    'array' => ':attribute ایک صف ہونی چاہیے۔',
    'before' => ':attribute:date سے پہلے کی تاریخ ہونی چاہیے۔',
    'before_or_equal' => ':attribute:date سے پہلے یا اس کے برابر تاریخ ہونی چاہیے۔',
    'کے درمیان' => [
        'numeric' => ':attribute:min اور :max کے درمیان ہونا چاہیے۔',
        'file' => ':attribute:min اور :max kilobytes کے درمیان ہونا چاہیے۔',
        'string' => ':attribute کو :min اور :max حروف کے درمیان ہونا چاہیے۔',
        'array' => ':attribute میں :min اور :max اشیاء کے درمیان ہونا ضروری ہے۔',
    ],
    'boolean' => ':attribute فیلڈ صحیح یا غلط ہونا چاہیے۔',
    'confirmed' => ':attribute تصدیق مماثل نہیں ہے۔',
    'date' => ':attribute درست تاریخ نہیں ہے۔',
    'date_equals' => ':attribute کو تاریخ کے برابر ہونا چاہیے:date۔',
    'date_format' => ':attribute فارمیٹ :format سے مماثل نہیں ہے۔',
    'different' => ':attribute اور :other مختلف ہونا چاہیے۔',
    'digits' => ':attribute کو :digits digits ہونا چاہیے۔',
    'digits_between' => ':attribute کو :min اور :max ہندسوں کے درمیان ہونا چاہیے۔',
    'dimensions' => ':attribute میں تصویر کے غلط جہت ہیں۔',
    'distinct' => ':attribute فیلڈ میں ڈپلیکیٹ ویلیو ہے۔',
    'email' => ':attribute ایک درست ای میل پتہ ہونا چاہیے۔',
    'ends_with' => ':attribute کو درج ذیل میں سے کسی ایک کے ساتھ ختم ہونا چاہیے: :values',
    'exists' => 'منتخب : وصف غلط ہے۔',
    'file' => ':attribute ایک فائل ہونی چاہیے۔',
    'filled' => ':attribute فیلڈ کی ایک قدر ہونی چاہیے۔',
    'gt' => [
        'numeric' => ':attribute کو :value سے بڑا ہونا چاہیے۔',
        'file' => ':attribute کو :value kilobytes سے بڑا ہونا چاہیے۔',
        'string' => ':attribute کو :value حروف سے بڑا ہونا چاہیے۔',
        'array' => ':attribute میں :value آئٹمز سے زیادہ ہونا ضروری ہے۔',
    ],
    'gte' => [
        'numeric' => ':attribute کو :value سے بڑا یا مساوی ہونا چاہیے۔',
        'file' => ':attribute کو :value kilobytes سے بڑا یا برابر ہونا چاہیے۔',
        'string' => ':attribute کو :value حروف سے بڑا یا مساوی ہونا چاہیے۔',
        'array' => ':attribute میں :value آئٹمز یا زیادہ ہونا چاہیے۔',
    ],
    'image' => ':attribute ایک تصویر ہونی چاہیے۔',
    'in' => 'منتخب : وصف غلط ہے۔',
    'in_array' => ':attribute فیلڈ :other میں موجود نہیں ہے۔',
    'integer' => ':attribute کو ایک عدد عدد ہونا چاہیے۔',
    'ip' => ':attribute ایک درست IP پتہ ہونا چاہیے۔',
    'ipv4' => ':attribute ایک درست IPv4 پتہ ہونا چاہیے۔',
    'ipv6' => ':attribute ایک درست IPv6 پتہ ہونا چاہیے۔',
    'json' => ':attribute ایک درست JSON سٹرنگ ہونی چاہیے۔',
    'lt' => [
        'numeric' => ':attribute کو :value سے کم ہونا چاہیے۔',
        'file' => ':attribute کو :value kilobytes سے کم ہونا چاہیے۔',
        'string' => ':attribute کو :value حروف سے کم ہونا چاہیے۔',
        'array' => ':attribute میں :value آئٹمز سے کم ہونا ضروری ہے۔',
    ],
    'lte' => [
        'numeric' => ':attribute کو :value سے کم یا برابر ہونا چاہیے۔',
        'file' => ':attribute کو :value kilobytes سے کم یا برابر ہونا چاہیے۔',
        'string' => ':attribute کو :value حروف سے کم یا برابر ہونا چاہیے۔',
        'array' => ':attribute میں :value آئٹمز سے زیادہ نہیں ہونا چاہیے۔',
    ],
    'max' => [
        'numeric' => 'یہ :attribute :max سے بڑا نہیں ہو سکتا',
        'file' => 'یہ :attribute :max kilobytes سے زیادہ نہیں ہو سکتا۔',
        'string' => ':attribute :max حروف سے زیادہ نہیں ہو سکتی۔',
        'array' => ':attribute میں :max سے زیادہ اشیاء نہیں ہو سکتیں۔',
    ],
    'mimes' => ':attribute کو ٹائپ: :values ​​کی فائل ہونی چاہیے۔',
    'mimetypes' => ':attribute کو ٹائپ: :values ​​کی فائل ہونی چاہیے۔',
    'min' => [
        'numeric' => 'The :attribute must be at least :min.',
        'file' => 'The :attribute must be at least :min kilobytes.',
        'string' => 'The :attribute must be at least :min characters.',
        'array' => 'The :attribute must have at least :min items.',
    ],
    'not_in' => 'منتخب : وصف غلط ہے۔',
    'not_regex' => ':attribute فارمیٹ غلط ہے۔',
    'numeric' => ':attribute کو ایک عدد ہونا چاہیے۔',
    'present' => ':attribute فیلڈ کا موجود ہونا ضروری ہے۔',
    'regex' => ':attribute فارمیٹ غلط ہے۔',
    'required' => ':attribute فیلڈ کی ضرورت ہے۔',
    'required_if' => ':attribute فیلڈ کی ضرورت ہوتی ہے جب :other ہے :value۔',
    'required_unless' => ':attribute فیلڈ کی ضرورت ہے جب تک کہ :other:values ​​میں نہ ہو۔',
    'required_with' => ':attribute فیلڈ کی ضرورت ہوتی ہے جب :values ​​موجود ہو۔',
    'required_with_all' => ':attribute فیلڈ کی ضرورت ہے جب :values ​​موجود ہوں۔',
    'required_without' => ':attribute فیلڈ کی ضرورت ہوتی ہے جب :values ​​موجود نہ ہو۔',
    'required_without_all' => ':attribute فیلڈ کی ضرورت ہوتی ہے جب :values ​​میں سے کوئی بھی موجود نہ ہو۔',
    'same' => ':attribute اور :other کو مماثل ہونا چاہیے۔',
    'سائز' => [
        'numeric' => ':attribute کو :size ہونا چاہیے۔',
        'file' => ':attribute:size kilobytes ہونا چاہیے۔',
        'string' => ':attribute کو :size حروف کا ہونا چاہیے۔',
        'array' => ':attribute میں :size آئٹمز ہونا ضروری ہے۔',
    ],
    'starts_with' => ':attribute کو درج ذیل میں سے کسی ایک سے شروع ہونا چاہیے: :values',
    'string' => ':attribute کو سٹرنگ ہونا چاہیے۔',
    'timezone' => ':attribute ایک درست زون ہونا چاہیے۔',
    'unique' => ':attribute پہلے ہی لیا جا چکا ہے۔',
    'uploaded' => ':attribute اپ لوڈ کرنے میں ناکام۔',
    'url' => ':attribute فارمیٹ غلط ہے۔',
    'uuid' => ':attribute کو ایک درست UUID ہونا چاہیے۔',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    
    'custom' => [
        'attribute-name' => [
            'rule-name' => 'اپنی مرضی کا پیغام',
        ],
        ],
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    
    'attributes' => [],
    'custom-messages' => [
        'quantity_not_available' => 'صرف :qty :unit دستیاب',
        'this_field_is_required' => 'یہ فیلڈ درکار ہے'
    ],

];
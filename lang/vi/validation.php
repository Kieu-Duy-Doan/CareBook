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

    'accepted'             => ':attribute phải được chấp nhận.',
    'accepted_if'          => ':attribute phải được chấp nhận khi :other là :value.',
    'active_url'           => ':attribute không phải là một đường dẫn hợp lệ.',
    'after'                => ':attribute phải là một ngày sau ngày :date.',
    'after_or_equal'       => ':attribute phải là một ngày sau hoặc bằng ngày :date.',
    'alpha'                => ':attribute chỉ được chứa các chữ cái.',
    'alpha_dash'           => ':attribute chỉ được chứa chữ cái, số, dấu gạch ngang và dấu gạch dưới.',
    'alpha_num'            => ':attribute chỉ được chứa chữ cái và số.',
    'any_of'               => ':attribute phải là một trong các giá trị đã chỉ định.',
    'array'                => ':attribute phải là một danh sách (mảng).',
    'ascii'                => ':attribute chỉ được chứa các ký tự chữ và số ASCII đơn byte.',
    'before'               => ':attribute phải là một ngày trước ngày :date.',
    'before_or_equal'      => ':attribute phải là một ngày trước hoặc bằng ngày :date.',
    'between'              => [
        'array'   => ':attribute phải có từ :min đến :max phần tử.',
        'file'    => ':attribute phải có dung lượng từ :min đến :max kilobytes.',
        'numeric' => ':attribute phải nằm trong khoảng từ :min đến :max.',
        'string'  => ':attribute phải có từ :min đến :max ký tự.',
    ],
    'boolean'              => ':attribute phải là đúng (true) hoặc sai (false).',
    'can'                  => ':attribute chứa giá trị không được phân quyền.',
    'confirmed'            => 'Xác nhận :attribute không khớp.',
    'contains'             => ':attribute còn thiếu một giá trị bắt buộc.',
    'current_password'     => 'Mật khẩu hiện tại không chính xác.',
    'date'                 => ':attribute không phải là định dạng ngày hợp lệ.',
    'date_equals'          => ':attribute phải là một ngày bằng với :date.',
    'date_format'          => ':attribute không khớp với định dạng :format.',
    'decimal'              => ':attribute phải có :decimal chữ số thập phân.',
    'declined'             => ':attribute phải bị từ chối.',
    'declined_if'          => ':attribute phải bị từ chối khi :other là :value.',
    'different'            => ':attribute và :other phải khác nhau.',
    'digits'               => ':attribute phải có đúng :digits chữ số.',
    'digits_between'       => ':attribute phải có từ :min đến :max chữ số.',
    'dimensions'           => ':attribute có kích thước hình ảnh không hợp lệ.',
    'distinct'             => ':attribute có giá trị bị trùng lặp.',
    'doesnt_end_with'      => ':attribute không được kết thúc bằng một trong các giá trị: :values.',
    'doesnt_start_with'    => ':attribute không được bắt đầu bằng một trong các giá trị: :values.',
    'email'                => ':attribute phải là một địa chỉ email hợp lệ.',
    'ends_with'            => ':attribute phải kết thúc bằng một trong các giá trị: :values.',
    'enum'                 => ':attribute đã chọn không hợp lệ.',
    'exists'               => ':attribute đã chọn không hợp lệ hoặc không tồn tại.',
    'extensions'           => ':attribute phải có phần mở rộng tệp là: :values.',
    'file'                 => ':attribute phải là một tệp tin.',
    'filled'               => ':attribute không được để trống.',
    'gt'                   => [
        'array'   => ':attribute phải có nhiều hơn :value phần tử.',
        'file'    => ':attribute phải có dung lượng lớn hơn :value kilobytes.',
        'numeric' => ':attribute phải lớn hơn :value.',
        'string'  => ':attribute phải dài hơn :value ký tự.',
    ],
    'gte'                  => [
        'array'   => ':attribute phải có từ :value phần tử trở lên.',
        'file'    => ':attribute phải có dung lượng lớn hơn hoặc bằng :value kilobytes.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :value.',
        'string'  => ':attribute phải có tối thiểu :value ký tự.',
    ],
    'hex_color'            => ':attribute phải là mã màu thập lục phân (HEX) hợp lệ.',
    'image'                => ':attribute phải là một hình ảnh.',
    'in'                   => ':attribute đã chọn không hợp lệ.',
    'in_array'             => ':attribute phải tồn tại trong :other.',
    'integer'              => ':attribute phải là một số nguyên.',
    'ip'                   => ':attribute phải là một địa chỉ IP hợp lệ.',
    'ipv4'                 => ':attribute phải là một địa chỉ IPv4 hợp lệ.',
    'ipv6'                 => ':attribute phải là một địa chỉ IPv6 hợp lệ.',
    'json'                 => ':attribute phải là một chuỗi JSON hợp lệ.',
    'list'                 => ':attribute phải là một danh sách.',
    'lowercase'            => ':attribute phải được viết thường.',
    'lt'                   => [
        'array'   => ':attribute phải có ít hơn :value phần tử.',
        'file'    => ':attribute phải có dung lượng nhỏ hơn :value kilobytes.',
        'numeric' => ':attribute phải nhỏ hơn :value.',
        'string'  => ':attribute phải ngắn hơn :value ký tự.',
    ],
    'lte'                  => [
        'array'   => ':attribute không được có nhiều hơn :value phần tử.',
        'file'    => ':attribute phải có dung lượng nhỏ hơn hoặc bằng :value kilobytes.',
        'numeric' => ':attribute phải nhỏ hơn hoặc bằng :value.',
        'string'  => ':attribute phải có tối đa :value ký tự.',
    ],
    'mac_address'          => ':attribute phải là một địa chỉ MAC hợp lệ.',
    'max'                  => [
        'array'   => ':attribute không được có nhiều hơn :max phần tử.',
        'file'    => ':attribute không được lớn hơn :max kilobytes.',
        'numeric' => ':attribute không được lớn hơn :max.',
        'string'  => ':attribute không được dài hơn :max ký tự.',
    ],
    'max_digits'           => ':attribute không được có nhiều hơn :max chữ số.',
    'mimes'                => ':attribute phải là một tệp có định dạng: :values.',
    'mimetypes'            => ':attribute phải là một tệp có định dạng: :values.',
    'min'                  => [
        'array'   => ':attribute phải có tối thiểu :min phần tử.',
        'file'    => ':attribute phải có dung lượng tối thiểu :min kilobytes.',
        'numeric' => ':attribute phải tối thiểu là :min.',
        'string'  => ':attribute phải có tối thiểu :min ký tự.',
    ],
    'min_digits'           => ':attribute phải có tối thiểu :min chữ số.',
    'missing'              => ':attribute không được xuất hiện.',
    'missing_if'           => ':attribute không được xuất hiện khi :other là :value.',
    'missing_unless'       => ':attribute không được xuất hiện trừ khi :other là :value.',
    'missing_with'         => ':attribute không được xuất hiện khi :values có mặt.',
    'missing_with_all'     => ':attribute không được xuất hiện khi tất cả :values có mặt.',
    'multiple_of'          => ':attribute phải là bội số của :value.',
    'not_in'               => ':attribute đã chọn không hợp lệ.',
    'not_regex'            => 'Định dạng của :attribute không hợp lệ.',
    'numeric'              => ':attribute phải là một số.',
    'password'             => [
        'letters'       => ':attribute phải chứa ít nhất một chữ cái.',
        'mixed'         => ':attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers'       => ':attribute phải chứa ít nhất một chữ số.',
        'symbols'       => ':attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => ':attribute này đã từng xuất hiện trong một vụ rò rỉ dữ liệu. Vui lòng chọn mật khẩu khác.',
    ],
    'present'              => ':attribute phải có mặt trong dữ liệu gửi lên.',
    'present_if'           => ':attribute phải có mặt khi :other là :value.',
    'present_unless'       => ':attribute phải có mặt trừ khi :other là :value.',
    'present_with'         => ':attribute phải có mặt khi :values có mặt.',
    'present_with_all'     => ':attribute phải có mặt khi tất cả :values có mặt.',
    'prohibited'           => ':attribute bị cấm gửi lên.',
    'prohibited_if'        => ':attribute bị cấm khi :other là :value.',
    'prohibited_if_accepted' => ':attribute bị cấm khi :other được chấp nhận.',
    'prohibited_if_declined' => ':attribute bị cấm khi :other bị từ chối.',
    'prohibited_unless'    => ':attribute bị cấm trừ khi :other nằm trong :values.',
    'prohibits'            => ':attribute ngăn chặn :other xuất hiện.',
    'regex'                => 'Định dạng :attribute không hợp lệ.',
    'required'             => 'Vui lòng nhập :attribute.',
    'required_array_keys'  => ':attribute phải chứa các khóa: :values.',
    'required_if'          => 'Vui lòng nhập :attribute khi :other là :value.',
    'required_if_accepted' => 'Vui lòng nhập :attribute khi :other được chấp nhận.',
    'required_if_declined' => 'Vui lòng nhập :attribute khi :other bị từ chối.',
    'required_unless'      => 'Vui lòng nhập :attribute trừ khi :other là :values.',
    'required_with'        => 'Vui lòng nhập :attribute khi có :values.',
    'required_with_all'    => 'Vui lòng nhập :attribute khi có tất cả :values.',
    'required_without'     => 'Vui lòng nhập :attribute khi không có :values.',
    'required_without_all' => 'Vui lòng nhập :attribute khi không có bất kỳ :values nào.',
    'same'                 => ':attribute và :other phải giống nhau.',
    'size'                 => [
        'array'   => ':attribute phải chứa đúng :size phần tử.',
        'file'    => ':attribute phải có dung lượng :size kilobytes.',
        'numeric' => ':attribute phải bằng :size.',
        'string'  => ':attribute phải có đúng :size ký tự.',
    ],
    'starts_with'          => ':attribute phải bắt đầu bằng một trong các giá trị: :values.',
    'string'               => ':attribute phải là một chuỗi ký tự.',
    'timezone'             => ':attribute phải là một múi giờ hợp lệ.',
    'unique'               => ':attribute đã tồn tại trong hệ thống.',
    'uploaded'             => ':attribute tải lên thất bại.',
    'uppercase'            => ':attribute phải được viết hoa.',
    'url'                  => ':attribute phải là một đường dẫn URL hợp lệ.',
    'ulid'                 => ':attribute phải là một chuỗi ULID hợp lệ.',
    'uuid'                 => ':attribute phải là một chuỗi UUID hợp lệ.',

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
        'password' => [
            'required'  => 'Vui lòng nhập mật khẩu.',
            'min'       => 'Mật khẩu tối thiểu :min ký tự.',
            'confirmed' => 'Xác nhận mật khẩu không khớp.',
            'letters'   => 'Mật khẩu phải chứa ít nhất một chữ cái.',
            'mixed'     => 'Mật khẩu phải chứa ít nhất một chữ hoa và một chữ thường.',
            'numbers'   => 'Mật khẩu phải chứa ít nhất một chữ số.',
            'symbols'   => 'Mật khẩu phải chứa ít nhất một ký tự đặc biệt.',
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

    'attributes' => [
        'name'                  => 'họ tên',
        'full_name'             => 'họ và tên',
        'username'              => 'tên đăng nhập',
        'email'                 => 'địa chỉ email',
        'phone'                 => 'số điện thoại',
        'password'              => 'mật khẩu',
        'password_confirmation' => 'xác nhận mật khẩu',
        'current_password'      => 'mật khẩu hiện tại',
        'avatar'                => 'ảnh đại diện',
        'date_of_birth'         => 'ngày sinh',
        'gender'                => 'giới tính',
        'address'               => 'địa chỉ',
        'id_card'               => 'số CMND/CCCD',
        'doctor_code'           => 'mã bác sĩ',
        'license_number'        => 'số chứng chỉ hành nghề',
        'specialty_id'          => 'chuyên khoa',
        'specialty_ids'         => 'danh sách chuyên khoa',
        'primary_specialty_id'  => 'chuyên khoa chính',
        'room_id'               => 'phòng khám',
        'appointment_date'      => 'ngày hẹn khám',
        'appointment_time'      => 'giờ hẹn khám',
        'note'                  => 'ghi chú',
        'description'           => 'mô tả',
        'title'                 => 'tiêu đề',
        'content'               => 'nội dung',
        'status'                => 'trạng thái',
        'reason'                => 'lý do',
    ],

];

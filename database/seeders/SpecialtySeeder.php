<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Specialty;

class SpecialtySeeder extends Seeder
{
    public function run(): void
    {
        $specialties = [
            ['name' => 'Tim mạch', 'desc' => 'Khám và điều trị các bệnh lý về tim mạch.', 'img' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Răng Hàm Mặt', 'desc' => 'Chuyên khoa về sức khỏe răng miệng.', 'img' => 'https://images.unsplash.com/photo-1606811841689-23dfddce3e95?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Nội tiêu hoá', 'desc' => 'Khám các bệnh lý dạ dày, đại tràng.', 'img' => 'https://images.unsplash.com/photo-1579684385127-1ef15d508118?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Nhi khoa', 'desc' => 'Khám chữa bệnh cho trẻ em.', 'img' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Thần kinh', 'desc' => 'Khám bệnh lý hệ thần kinh trung ương và ngoại vi.', 'img' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Cơ xương khớp', 'desc' => 'Chuyên điều trị bệnh lý cơ xương khớp.', 'img' => 'https://images.unsplash.com/photo-1584515933487-779824d29309?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Da liễu', 'desc' => 'Chuyên khoa da liễu, thẩm mỹ da.', 'img' => 'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Mắt', 'desc' => 'Khám và đo thị lực, điều trị bệnh về mắt.', 'img' => 'https://images.unsplash.com/photo-1512428559087-560fa5ceab42?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Tai Mũi Họng', 'desc' => 'Khám bệnh lý Tai, Mũi, Họng.', 'img' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?auto=format&fit=crop&q=80&w=300&h=300'],
            ['name' => 'Nội tiết', 'desc' => 'Khám tiểu đường, tuyến giáp và các bệnh nội tiết.', 'img' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&q=80&w=300&h=300'],
        ];

        foreach ($specialties as $index => $spec) {
            Specialty::create([
                'name' => $spec['name'],
                'description' => $spec['desc'],
                'image_url' => $spec['img'],
                'display_order' => $index + 1,
            ]);
        }
    }
}

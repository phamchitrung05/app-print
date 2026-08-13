<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Form - Malibu Print</title>
    <!-- Tailwind CSS v4 CDN -->
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f3f4f6;
            padding: 20px;
        }
        .print-container {
            width: 1100px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            position: relative;
            overflow: hidden;
        }
        /* Decor top right */
        .decor-top-right {
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 100px;
            background-image: radial-gradient(circle at 100% 0%, #1a73e8 15%, #ff007f 40%, #ffeb3b 65%, #000 90%);
            border-bottom-left-radius: 100%;
            opacity: 0.8;
            z-index: 0;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 30% 0%);
        }
    </style>
</head>
<body class="text-gray-800 text-sm">

<div class="print-container p-8">
    <!-- HEADER -->
    <header class="flex justify-between items-start border-b border-gray-200 pb-4 mb-8 relative z-10">
        <!-- Left: Logo & Contact -->
        <div class="flex items-center gap-8">
            <!-- Logo -->
            <div class="text-center">
                <div class="relative w-32 h-16 mb-1">
                    <!-- Abstract Logo Graphic Placeholder -->
                    <div class="absolute inset-0 flex justify-center items-end pb-2">
                        <div class="w-24 h-12 rounded-t-full bg-gradient-to-r from-blue-500 via-pink-500 to-yellow-400"></div>
                        <div class="w-16 h-8 rounded-t-full bg-white absolute bottom-2"></div>
                    </div>
                </div>
                <h1 class="text-2xl font-bold">
                    <span class="text-orange-500">Malibu</span> <span class="text-blue-500">PRINT</span>
                </h1>
                <p class="text-[9px] text-orange-500 tracking-widest font-semibold">DESIGN AND PRINTING</p>
            </div>

            <!-- Contact Info -->
            <div class="space-y-2 text-xs">
                <p class="flex items-center gap-2"><i class="fa-solid fa-location-dot text-blue-500 w-4"></i> 123 Nguyễn Văn Linh, Hải Châu, Đà Nẵng</p>
                <p class="flex items-center gap-2"><i class="fa-solid fa-phone text-blue-500 w-4"></i> 0905 123 456</p>
                <p class="flex items-center gap-2"><i class="fa-solid fa-envelope text-blue-500 w-4"></i> malibu.print@gmail.com</p>
                <p class="flex items-center gap-2"><i class="fa-solid fa-globe text-blue-500 w-4"></i> www.malibuprint.vn</p>
            </div>
        </div>

        <!-- Right: Order Info -->
        <div class="text-right border-l border-gray-200 pl-8 pr-12 relative z-10">
            <h2 class="text-4xl font-bold text-orange-500 tracking-wider">ORDER</h2>
            <div class="bg-blue-600 text-white text-xs font-bold py-1 px-4 rounded inline-block mt-1 mb-3">
                #ORD-2026-0812-001
            </div>

            <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-xs text-left">
                <p><i class="fa-regular fa-calendar text-gray-500 w-4"></i> Ngày đặt hàng <span class="float-right">:</span></p>
                <p>12/08/2026</p>

                <p><i class="fa-regular fa-file-lines text-gray-500 w-4"></i> Số trang <span class="float-right">:</span></p>
                <p>1/1</p>

                <p><i class="fa-regular fa-calendar-check text-gray-500 w-4"></i> Ngày giao hàng <span class="float-right">:</span></p>
                <p>15/08/2026</p>

                <p><i class="fa-regular fa-user text-gray-500 w-4"></i> Nhân viên <span class="float-right">:</span></p>
                <p>Nguyễn Văn A</p>
            </div>
        </div>
        <!-- Decorative corner -->
        <div class="decor-top-right"></div>
    </header>

    <!-- INFO SECTION (Customer & Order) -->
    <div class="grid grid-cols-2 gap-6 mb-8">
        <!-- Customer Info -->
        <div class="border border-blue-400 rounded-md p-4 pt-5 relative">
            <div class="absolute -top-3 left-4 bg-blue-500 text-white text-xs font-bold py-1 px-3 rounded-full flex items-center gap-2">
                <i class="fa-solid fa-user"></i> THÔNG TIN KHÁCH HÀNG
            </div>
            <div class="grid grid-cols-[100px_10px_1fr] gap-y-2 text-xs">
                <div class="font-semibold text-gray-700">Tên khách hàng</div><div>:</div><div class="font-bold">Công Ty TNHH ABC</div>
                <div class="font-semibold text-gray-700">Địa chỉ</div><div>:</div><div>45 Lê Duẩn, Hải Châu, Đà Nẵng</div>
                <div class="font-semibold text-gray-700">Điện thoại</div><div>:</div><div>0905 987 654</div>
                <div class="font-semibold text-gray-700">Email</div><div>:</div><div>contact@abc.com</div>
                <div class="font-semibold text-gray-700">Mã số thuế</div><div>:</div><div>0401234567</div>
            </div>
        </div>

        <!-- Order Info -->
        <div class="border border-orange-400 rounded-md p-4 pt-5 relative">
            <div class="absolute -top-3 left-4 bg-orange-500 text-white text-xs font-bold py-1 px-3 rounded-full flex items-center gap-2">
                <i class="fa-solid fa-clipboard-list"></i> THÔNG TIN ĐƠN HÀNG
            </div>
            <div class="grid grid-cols-[120px_10px_1fr] gap-y-2 text-xs">
                <div class="font-semibold text-gray-700">Loại đơn hàng</div><div>:</div><div>In ấn</div>
                <div class="font-semibold text-gray-700">Hình thức thanh toán</div><div>:</div><div>Chuyển khoản</div>
                <div class="font-semibold text-gray-700">Trạng thái</div><div>:</div><div class="text-red-500 font-bold">Chờ sản xuất</div>
                <div class="font-semibold text-gray-700">Ghi chú</div><div>:</div><div>In test màu trước khi in số lượng lớn</div>
            </div>
        </div>
    </div>

    <!-- TABLE SECTION -->
    <table class="w-full text-xs border-collapse border border-gray-300 mb-6 text-center">
        <thead>
        <tr class="bg-[#1877f2] text-white">
            <th class="border border-gray-300 p-2 w-[5%]">STT</th>
            <th class="border border-gray-300 p-2 w-[25%] text-left">TÊN SẢN PHẨM</th>
            <th class="border border-gray-300 p-2 w-[35%] text-left">QUY CÁCH / MÔ TẢ</th>
            <th class="border border-gray-300 p-2 w-[10%]">SỐ LƯỢNG</th>
            <th class="border border-gray-300 p-2 w-[10%]">ĐƠN GIÁ</th>
            <th class="border border-gray-300 p-2 w-[15%]">THÀNH TIỀN</th>
        </tr>
        </thead>
        <tbody>
        <!-- Item 1 -->
        <tr class="border-b border-gray-200 bg-gray-50/30">
            <td class="p-3">1</td>
            <td class="p-3 text-left font-bold flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-200 rounded flex-shrink-0 border border-gray-300 overflow-hidden relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-900 to-black"></div>
                </div>
                Danh thiếp
            </td>
            <td class="p-3 text-left text-[11px] text-gray-700 leading-tight">
                Kích thước: 9 x 5.5 cm<br>
                Giấy: Couche 300gsm<br>
                Cán mờ 2 mặt
            </td>
            <td class="p-3">500 Hộp</td>
            <td class="p-3">120.000</td>
            <td class="p-3 font-bold text-red-600">60.000.000</td>
        </tr>
        <!-- Item 2 -->
        <tr class="border-b border-gray-200">
            <td class="p-3">2</td>
            <td class="p-3 text-left font-bold flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded flex-shrink-0 border border-gray-300 relative overflow-hidden">
                    <div class="absolute inset-0 bg-green-600 opacity-20"></div>
                </div>
                Tờ rơi A5
            </td>
            <td class="p-3 text-left text-[11px] text-gray-700 leading-tight">
                Kích thước: A5 (14.8 x 21 cm)<br>
                Giấy: Couche 150gsm<br>
                In 2 mặt
            </td>
            <td class="p-3">1.000 Tờ</td>
            <td class="p-3">1.200</td>
            <td class="p-3 font-bold text-red-600">1.200.000</td>
        </tr>
        <!-- Item 3 -->
        <tr class="border-b border-gray-200 bg-gray-50/30">
            <td class="p-3">3</td>
            <td class="p-3 text-left font-bold flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded flex-shrink-0 border border-gray-300 relative overflow-hidden">
                    <div class="absolute inset-0 bg-blue-900 opacity-80"></div>
                </div>
                Poster A3
            </td>
            <td class="p-3 text-left text-[11px] text-gray-700 leading-tight">
                Kích thước: A3 (29.7 x 42 cm)<br>
                Giấy: Couche 200gsm<br>
                In 1 mặt
            </td>
            <td class="p-3">200 Tờ</td>
            <td class="p-3">3.500</td>
            <td class="p-3 font-bold text-red-600">700.000</td>
        </tr>
        <!-- Item 4 -->
        <tr class="border-b border-gray-200">
            <td class="p-3">4</td>
            <td class="p-3 text-left font-bold flex items-center gap-3">
                <div class="w-10 h-10 bg-gray-100 rounded flex-shrink-0 border border-gray-300 relative overflow-hidden flex gap-1 p-1">
                    <div class="w-1/3 bg-gray-400 h-full"></div>
                    <div class="w-1/3 bg-gray-300 h-full"></div>
                    <div class="w-1/3 bg-gray-400 h-full"></div>
                </div>
                Brochure gấp 3
            </td>
            <td class="p-3 text-left text-[11px] text-gray-700 leading-tight">
                Kích thước: A4 gấp 3<br>
                Giấy: Couche 150gsm<br>
                In 2 mặt, cán mờ
            </td>
            <td class="p-3">500 Tờ</td>
            <td class="p-3">2.800</td>
            <td class="p-3 font-bold text-red-600">1.400.000</td>
        </tr>
        <!-- Item 5 -->
        <tr class="border-b border-gray-200 bg-gray-50/30">
            <td class="p-3">5</td>
            <td class="p-3 text-left font-bold flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex-shrink-0 border border-gray-300 relative overflow-hidden bg-yellow-400 flex items-center justify-center">
                    <div class="w-6 h-6 bg-blue-500 rounded-full"></div>
                </div>
                Decal dán
            </td>
            <td class="p-3 text-left text-[11px] text-gray-700 leading-tight">
                Kích thước: 5 x 5 cm (tròn)<br>
                Chất liệu: Decal giấy<br>
                Bế theo file
            </td>
            <td class="p-3">1.000 Tem</td>
            <td class="p-3">500</td>
            <td class="p-3 font-bold text-red-600">500.000</td>
        </tr>
        </tbody>
    </table>

    <!-- BOTTOM SECTION -->
    <div class="grid grid-cols-[30%_35%_35%] gap-4 mb-10">
        <!-- Notes -->
        <div class="bg-gray-50 border border-gray-200 rounded p-3 text-[10px]">
            <h4 class="font-bold text-blue-600 mb-2 flex items-center gap-1"><i class="fa-solid fa-file-lines"></i> GHI CHÚ</h4>
            <ul class="space-y-1 text-gray-600">
                <li class="flex gap-1"><i class="fa-solid fa-circle-check text-blue-500 mt-[2px]"></i> Vui lòng kiểm tra kỹ nội dung, chính tả trước khi duyệt in.</li>
                <li class="flex gap-1"><i class="fa-solid fa-circle-check text-blue-500 mt-[2px]"></i> Thời gian giao hàng có thể thay đổi tùy theo khối lượng thực tế.</li>
                <li class="flex gap-1"><i class="fa-solid fa-circle-check text-blue-500 mt-[2px]"></i> Cảm ơn quý khách đã tin tưởng và sử dụng dịch vụ của chúng tôi!</li>
            </ul>
        </div>

        <!-- Payment Info -->
        <div class="bg-orange-50 border border-orange-100 rounded p-3 flex gap-4 items-center">
            <div class="w-16 h-16 bg-white border border-gray-300 p-1 flex-shrink-0">
                <!-- Fake QR Code -->
                <svg viewBox="0 0 100 100" class="w-full h-full text-black fill-current">
                    <path d="M0 0h30v30H0zM10 10h10v10H10zM70 0h30v30H70zM80 10h10v10H80zM0 70h30v30H0zM10 80h10v10H10zM40 0h20v10H40zM50 20h10v20H50zM40 40h20v20H40zM80 50h20v10H80zM70 70h10v20H70zM90 80h10v20H90zM40 80h20v10H40z"/>
                </svg>
            </div>
            <div class="text-[11px]">
                <h4 class="font-bold text-orange-600 mb-1">THÔNG TIN THANH TOÁN</h4>
                <div class="grid grid-cols-[80px_10px_1fr] gap-y-1">
                    <div class="font-semibold text-gray-700">Ngân hàng</div><div>:</div><div class="font-bold">Vietcombank</div>
                    <div class="font-semibold text-gray-700">Số tài khoản</div><div>:</div><div class="font-bold tracking-widest">1234 5678 9012</div>
                    <div class="font-semibold text-gray-700">Chủ tài khoản</div><div>:</div><div class="font-bold">MALIBU PRINT</div>
                </div>
            </div>
        </div>

        <!-- Totals -->
        <div class="border border-gray-200 rounded flex flex-col justify-between overflow-hidden text-[12px]">
            <div class="p-3 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Tổng tiền hàng</span>
                    <span class="font-semibold">63.800.000</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Chiết khấu</span>
                    <span class="font-semibold">3.800.000</span>
                </div>
            </div>
            <div>
                <div class="bg-[#ff5722] text-white p-2 px-3 flex justify-between items-center font-bold text-sm">
                    <span>TỔNG THANH TOÁN</span>
                    <span class="text-lg">60.000.000 VNĐ</span>
                </div>
                <div class="text-center text-[10px] text-gray-500 py-1 italic bg-gray-50">
                    (Sáu mươi triệu đồng chẵn)
                </div>
            </div>
        </div>
    </div>

    <!-- SIGNATURES -->
    <div class="grid grid-cols-3 text-center text-xs pt-4">
        <div class="flex flex-col items-center">
            <div class="font-bold text-blue-600 flex items-center gap-1 mb-1">
                <i class="fa-solid fa-user-circle"></i> KHÁCH HÀNG
            </div>
            <div class="italic text-gray-500 mb-16">(Ký, ghi rõ họ tên)</div>
            <div class="w-48 border-t border-dashed border-gray-400 pt-2">
                Ngày ...... tháng ...... năm 2026
            </div>
        </div>

        <div class="flex flex-col items-center">
            <div class="font-bold text-orange-500 flex items-center gap-1 mb-1">
                <i class="fa-solid fa-user-tie"></i> NHÂN VIÊN KINH DOANH
            </div>
            <div class="italic text-gray-500 mb-16">(Ký, ghi rõ họ tên)</div>
            <div class="w-48 border-t border-dashed border-gray-400 pt-2"></div>
        </div>

        <div class="flex flex-col items-center">
            <div class="font-bold text-pink-600 flex items-center gap-1 mb-1">
                <i class="fa-solid fa-user-shield"></i> QUẢN LÝ
            </div>
            <div class="italic text-gray-500 mb-16">(Ký, ghi rõ họ tên)</div>
            <div class="w-48 border-t border-dashed border-gray-400 pt-2"></div>
        </div>
    </div>

    <!-- Decor Bottom -->
    <div class="absolute bottom-0 left-0 w-full h-2 flex">
        <div class="h-full bg-blue-500 w-1/3"></div>
        <div class="h-full bg-yellow-400 w-1/3"></div>
        <div class="h-full bg-pink-500 w-1/3"></div>
    </div>
</div>

</body>
</html>

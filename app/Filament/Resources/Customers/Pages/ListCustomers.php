<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Services\CustomerExcelImporter;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Throwable;

class ListCustomers extends ListRecords
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->schema([
                    FileUpload::make('file')
                        ->label('File Excel')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->rules(['extensions:xlsx,xls'])
                        ->required()
                        ->storeFiles(false),
                ])
                ->action(function (array $data): void {
                    try {
                        $result = app(CustomerExcelImporter::class)->import(
                            $data['file']->getRealPath(),
                        );

                        $body = "Đã import {$result['imported']} khách hàng.";

                        if ($result['skipped'] > 0) {
                            $body .= " Bỏ qua {$result['skipped']} dòng trống.";
                        }

                        if ($result['errors'] !== []) {
                            $body .= ' Có '.count($result['errors']).' dòng lỗi.';
                        }

                        Notification::make()
                            ->title('Import khách hàng hoàn tất')
                            ->body($body)
                            ->when(
                                $result['errors'] !== [],
                                fn (Notification $notification): Notification => $notification->warning(),
                            )
                            ->when(
                                $result['errors'] === [],
                                fn (Notification $notification): Notification => $notification->success(),
                            )
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Import khách hàng thất bại')
                            ->body($exception->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                })
                ->modalHeading('Import khách hàng từ Excel')
                ->modalSubmitActionLabel('Import')
                ->modalWidth('xl'),

            CreateAction::make()
                ->label('Tạo mới')
                ->modalHeading('Thêm khách hàng mới')
                ->schema(fn (Schema $schema): Schema => CustomerForm::configure($schema))
                ->modalWidth('7xl'),
        ];
    }
}

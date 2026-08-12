<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Resources\Customers\Schemas\CustomerForm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Họ và tên')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('Số điện thoại')
                    ->searchable()
                    ->copyable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Địa chỉ')
                    ->limit(40)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Hoạt động')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('uuid')
                    ->label('UUID')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Ngày tạo')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Cập nhật')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Trạng thái')
                    ->placeholder('Tất cả khách hàng')
                    ->trueLabel('Đang hoạt động')
                    ->falseLabel('Ngừng hoạt động'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Chỉnh sửa')
                    ->modalHeading(fn ($record): string => "Chỉnh sửa khách hàng: {$record->name}")
                    ->schema(fn (Schema $schema): Schema => CustomerForm::configure($schema))
                    ->modalWidth('7xl'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

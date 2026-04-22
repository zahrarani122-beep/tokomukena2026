<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MotorResource\Pages;
use App\Models\Motor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;


class MotorResource extends Resource
{
    protected static ?string $model = Motor::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Motor';
    protected static ?string $pluralModelLabel = 'Data Motor';

    public static function form(Form $form): Form
    {
        return $form->schema([

            TextInput::make('nama_motor')
                ->label('Nama Motor')
                ->required(),

            Select::make('jenis_motor')
                ->options([
                    'matic' => 'Matic',
                    'sport' => 'Sport',
                    'bebek' => 'Bebek',
                ])
                ->required(),

            TextInput::make('merek_motor')
                ->label('Merek Motor')
                ->required(),

            TextInput::make('plat_nomor')
                ->label('Plat Nomor')
                ->required()
                ->unique(ignoreRecord: true),

            Select::make('status')
                ->options([
                    'tersedia' => 'Tersedia',
                    'disewa' => 'Disewa',
                ])
                ->default('tersedia')
                ->required(),

            TextInput::make('sewa_perhari')
                ->label('Sewa per Hari')
                ->numeric()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_motor')->searchable(),
                TextColumn::make('jenis_motor'),
                TextColumn::make('merek_motor'),
                TextColumn::make('plat_nomor')->searchable(),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'tersedia',
                        'danger' => 'disewa',
                    ]),
                TextColumn::make('sewa_perhari')
                    ->money('IDR'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'tersedia' => 'Tersedia',
                        'disewa' => 'Disewa',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMotors::route('/'),
            'create' => Pages\CreateMotor::route('/create'),
            'edit' => Pages\EditMotor::route('/{record}/edit'),
        ];
    }
}
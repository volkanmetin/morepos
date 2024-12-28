<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Actions\Tenant\CreateTenantAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use App\Models\User;
class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),

            Actions\Action::make('create-tenant')
                ->label('Create Tenant')
                ->modalWidth('md')
                ->form([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                        
                    Forms\Components\TextInput::make('domain')
                        ->required()
                        ->maxLength(255)
                        //->unique('tenants', 'domain', fn ($record) => $record . '.' . config('app.domain'))
                        ->suffix('.' . config('app.domain')),

                    TextInput::make('database')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Select::make('user')
                        ->label('Kullanıcı')
                        ->options(\App\Models\User::pluck('name', 'id'))
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('password')
                                ->password()
                                ->required()
                                ->maxLength(255)
                        ]),
                ])
                ->action(function (array $data): void {
                    $data['domain'] = $data['domain'] . '.' . config('app.domain');
                    $user = User::find($data['user']);
                    (new CreateTenantAction())->execute($data, $user);
                })
        ];
    }
}

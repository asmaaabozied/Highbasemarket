<?php

namespace App\Services\Whatsapp;

use App\Enum\WhatsAppButtonTypeEnum;
use App\Enum\WhatsAppTemplate;

readonly class WhatsAppTemplateConfig
{
    public function __construct(
        private array $header = [],
        private array $bodyVariables = [],
        private array $buttons = []
    ) {}

    public static function fromTemplate(WhatsAppTemplate $template): self
    {
        return match ($template) {
            WhatsAppTemplate::WELCOME            => new self(bodyVariables: ['user.name']),
            WhatsAppTemplate::OTP                => new self(bodyVariables: ['code']),
            WhatsAppTemplate::ORDER_CONFIRMATION => new self(bodyVariables: ['order.number']),
            WhatsAppTemplate::SHIPPING_UPDATE    => new self(bodyVariables: ['tracking.number', 'status']),
            WhatsAppTemplate::FEEDBACK_REQUEST   => new self(bodyVariables: ['order.id', 'feedback.link']),
            WhatsAppTemplate::HELLO_WORLD        => new self(bodyVariables: ['name']),

            WhatsAppTemplate::LS_ACCOUNT_STARTED, WhatsAppTemplate::LS_VISIT_REMINDER, WhatsAppTemplate::ACCOUNT_ACCESS_PREPARED => new self(
                bodyVariables: ['user.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_PRICINGTEMPLATE_SUBMITTED => new self(bodyVariables: ['user.name', 'template.name']),
            WhatsAppTemplate::LS_CREDITTEMPLATE_ASSIGNED   => new self(bodyVariables: ['user.name', 'credit.amount']),
            WhatsAppTemplate::LS_CREDIT_LIMITUPDATED       => new self(bodyVariables: ['new.limit', 'previous.limit']),
            WhatsAppTemplate::LS_STOCK_BULKUPLOAD          => new self(bodyVariables: ['count', 'branch.name']),
            WhatsAppTemplate::LS_BRANCH_CREATED            => new self(
                bodyVariables: ['user.name', 'branch.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LB_ORDER_CANCELLED,
            WhatsAppTemplate::LS_ORDER_READYTODISPATCH,
            WhatsAppTemplate::LB_ORDER_CONFIRMED,
            WhatsAppTemplate::LB_ORDER_REJECTED,
            WhatsAppTemplate::LB_ORDER_DELIVERED => new self(
                bodyVariables: ['user.name', 'order.number', 'vendor.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_ORDER_RECEIVED => new self(
                bodyVariables: ['user.name', 'from.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_CUSTOMER_LINKED => new self(bodyVariables: ['customer.name', 'business.name']),
            WhatsAppTemplate::LS_CUSTOMER_ADDED  => new self(
                bodyVariables: ['user.name', 'customer'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_TEAMMEMBER_ADDED => new self(
                bodyVariables: ['user.name', 'employee.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LB_VISIT_CONFIRMED_BUYER, WhatsAppTemplate::TIMELINE_UPDATED => new self(
                bodyVariables: ['user.name', 'company.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_STOCK_ADDED, WhatsAppTemplate::LS_PRODUCT_ADDED => new self(
                bodyVariables: ['user.name', 'product.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_PRODUCTS_BULKUPLOAD => new self(bodyVariables: ['count', 'category']),
            WhatsAppTemplate::LS_PRODUCT_BULKUPDATE  => new self(bodyVariables: ['count', 'updated.fields']),
            WhatsAppTemplate::LS_BRAND_ADDED,
            WhatsAppTemplate::LS_BRAND_CLAIM_APPROVED,
            WhatsAppTemplate::LS_BRAND_CLAIMED => new self(
                bodyVariables: ['user.name', 'brand.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_BRAND_CLAIM_REJECTED => new self(bodyVariables: ['brand.name', 'reason']),
            WhatsAppTemplate::GB_RFQ_SENT             => new self(
                bodyVariables: ['user.name', 'rfq.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::GB_QUOTE_STATUSUPDATED => new self(bodyVariables: ['user.name', 'status']),
            WhatsAppTemplate::GB_PROPOSAL_RECEIVED   => new self(
                bodyVariables: ['user.name', 'rfq.title'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::GB_VENDOR_LINKED => new self(
                bodyVariables: ['user.name', 'vendor.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_VISIT_SCHEDULED,
            WhatsAppTemplate::LS_VISIT_MANUAL_ADDED,
            WhatsAppTemplate::LS_VISIT_POINT_ADDED_MG, WhatsAppTemplate::LS_EXTERNAL_CUSTOMER_CREATED, WhatsAppTemplate::LB_VISIT_CONFIRMED_EXTERNAL_BUYER => new self(
                bodyVariables: ['user.name', 'customer.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_VISIT_DATE_CHANGED_TM,
            WhatsAppTemplate::LS_VISIT_DATE_CHANGED_MG => new self(
                bodyVariables: ['user.name', 'customer.name', 'new_date', 'reason'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_VISIT_POSTPONED_TM => new self(
                bodyVariables: ['user.name', 'customer.name', 'reason'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_VISIT_CONFIRMED => new self(
                bodyVariables: ['user.name', 'employee.name', 'customer.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_VISIT_POSTPONED_MG => new self(
                bodyVariables: ['user.name', 'employee.name', 'customer.name', 'reason'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_VISIT_MISSED => new self(
                bodyVariables: ['user.name', 'customer.name', 'employee.name'],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),

            WhatsAppTemplate::LS_VISIT_REPORT_DAILY,
            WhatsAppTemplate::LS_VISIT_REPORT_WEEKLY,
            WhatsAppTemplate::LS_VISIT_REPORT_MONTHLY => new self(
                bodyVariables: [
                    'user.name', 'data.completed', 'data.missed', 'data.postponed', 'data.date_changed',
                ],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_VISIT_REPORT_DAILY_MG,
            WhatsAppTemplate::LS_VISIT_REPORT_WEEKLY_MG,
            WhatsAppTemplate::LS_VISIT_REPORT_MONTHLY_MG => new self(
                bodyVariables: [
                    'user.name', 'data.branch', 'data.total', 'data.completed', 'data.missed', 'data.date_changed',
                    'data.postponed',
                ],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_INVOICE_CREATED, WhatsAppTemplate::LS_INVOICE_CREDIT_IMPACT, WhatsAppTemplate::LS_INVOICE_CASH_INVOICE => new self(
                bodyVariables: [
                    'user.name', 'customer.name',
                ],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_INVOICE_CREATED_MANAGER => new self(
                bodyVariables: [
                    'user.name', 'employee.name', 'customer.name',
                ],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LB_INVOICE_CREDIT_USED,
            WhatsAppTemplate::LB_INVOICE_CREATED_CUSTOMER,
            WhatsAppTemplate::LB_INVOICE_EXTERNAL_CREATED => new self(
                bodyVariables: [
                    'user.name', 'seller.name',
                ],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_INVOICE_REPORT_WEEKLY_REP => new self(
                bodyVariables: [
                    'user.name', 'data.total_invoices', 'data.cash', 'data.credit', 'data.total_customers',
                ],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::LS_INVOICE_REPORT_WEEKLY_M => new self(
                bodyVariables: [
                    'user.name',
                    'data.branch',
                    'data.total_invoices',
                    'data.cash',
                    'data.credit',
                    'data.total_customers',
                ],
                buttons: [new WhatsAppButton(WhatsAppButtonTypeEnum::VISIT_WEBSITE, 'link')]
            ),
            WhatsAppTemplate::HB_CATALOGUE_DELIVERY => new self(bodyVariables: [], buttons: []),
            default                                 => new self
        };
    }

    public function getHeader(): array
    {
        return $this->header;
    }

    public function getBodyVariables(): array
    {
        return $this->bodyVariables;
    }

    public function getButtons(): array
    {
        return $this->buttons;
    }
}

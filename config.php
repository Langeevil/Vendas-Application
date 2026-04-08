<?php

$apiBaseUrl = 'http://localhost:8090';

$entityConfigs = [
    'ufs' => [
        'label' => 'UFs',
        'singular' => 'UF',
        'endpoint' => '/ufs',
        'primary_keys' => ['coduf'],
        'summary' => 'Estados e siglas para composicao de endereco.',
        'fields' => [
            ['name' => 'nomeuf', 'label' => 'Nome da UF', 'type' => 'text', 'required' => true],
            ['name' => 'siglauf', 'label' => 'Sigla', 'type' => 'text', 'required' => true],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'coduf'],
            ['label' => 'Nome', 'path' => 'nomeuf'],
            ['label' => 'Sigla', 'path' => 'siglauf'],
        ],
    ],
    'cidades' => [
        'label' => 'Cidades',
        'singular' => 'Cidade',
        'endpoint' => '/cidades',
        'primary_keys' => ['codcidade'],
        'summary' => 'Cidades vinculadas a uma UF.',
        'fields' => [
            ['name' => 'nomecidade', 'label' => 'Nome da Cidade', 'type' => 'text', 'required' => true],
            ['name' => 'nomeuf', 'label' => 'UF', 'type' => 'select', 'required' => true, 'options_resource' => 'ufs', 'option_value' => 'nomeuf', 'option_label' => 'nomeuf', 'source' => 'uf.nomeuf'],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codcidade'],
            ['label' => 'Cidade', 'path' => 'nomecidade'],
            ['label' => 'UF', 'path' => 'uf.siglauf'],
        ],
    ],
    'ceps' => [
        'label' => 'CEPs',
        'singular' => 'CEP',
        'endpoint' => '/ceps',
        'primary_keys' => ['codcep'],
        'summary' => 'Cadastro simples de CEPs.',
        'fields' => [
            ['name' => 'numerocep', 'label' => 'CEP', 'type' => 'text', 'required' => true],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codcep'],
            ['label' => 'CEP', 'path' => 'numeroCep'],
        ],
    ],
    'bairros' => [
        'label' => 'Bairros',
        'singular' => 'Bairro',
        'endpoint' => '/bairros',
        'primary_keys' => ['codbairro'],
        'summary' => 'Bairros para composicao de endereco.',
        'fields' => [
            ['name' => 'nomebairro', 'label' => 'Nome do Bairro', 'type' => 'text', 'required' => true],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codbairro'],
            ['label' => 'Bairro', 'path' => 'nomebairro'],
        ],
    ],
    'ruas' => [
        'label' => 'Ruas',
        'singular' => 'Rua',
        'endpoint' => '/ruas',
        'primary_keys' => ['codrua'],
        'summary' => 'Ruas disponiveis para clientes e fornecedores.',
        'fields' => [
            ['name' => 'nomerua', 'label' => 'Nome da Rua', 'type' => 'text', 'required' => true],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codrua'],
            ['label' => 'Rua', 'path' => 'nomerua'],
        ],
    ],
    'sexos' => [
        'label' => 'Sexos',
        'singular' => 'Sexo',
        'endpoint' => '/sexos',
        'primary_keys' => ['codsexo'],
        'summary' => 'Cadastro auxiliar para clientes.',
        'fields' => [
            ['name' => 'nomesexo', 'label' => 'Descricao', 'type' => 'text', 'required' => true],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codsexo'],
            ['label' => 'Descricao', 'path' => 'nomesexo'],
        ],
    ],
    'tipos' => [
        'label' => 'Tipos',
        'singular' => 'Tipo',
        'endpoint' => '/tipos',
        'primary_keys' => ['codtipo'],
        'summary' => 'Classificacao de produtos.',
        'fields' => [
            ['name' => 'nometipo', 'label' => 'Nome do Tipo', 'type' => 'text', 'required' => true],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codtipo'],
            ['label' => 'Tipo', 'path' => 'nometipo'],
        ],
    ],
    'marcas' => [
        'label' => 'Marcas',
        'singular' => 'Marca',
        'endpoint' => '/marcas',
        'primary_keys' => ['codmarca'],
        'summary' => 'Marcas associadas aos produtos.',
        'fields' => [
            ['name' => 'nomemarca', 'label' => 'Nome da Marca', 'type' => 'text', 'required' => true],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codmarca'],
            ['label' => 'Marca', 'path' => 'nomemarca'],
        ],
    ],
    'clientes' => [
        'label' => 'Clientes',
        'singular' => 'Cliente',
        'endpoint' => '/clientes',
        'primary_keys' => ['codcliente'],
        'summary' => 'Clientes com dados basicos e endereco.',
        'fields' => [
            ['name' => 'nomecliente', 'label' => 'Nome do Cliente', 'type' => 'text', 'required' => true],
            ['name' => 'codsexo', 'label' => 'Sexo', 'type' => 'select', 'required' => true, 'options_resource' => 'sexos', 'option_value' => 'codsexo', 'option_label' => 'nomesexo', 'source' => 'sexo.codsexo'],
            ['name' => 'codrua', 'label' => 'Rua', 'type' => 'select', 'required' => true, 'options_resource' => 'ruas', 'option_value' => 'codrua', 'option_label' => 'nomerua', 'source' => 'rua.codrua'],
            ['name' => 'codbairro', 'label' => 'Bairro', 'type' => 'select', 'required' => true, 'options_resource' => 'bairros', 'option_value' => 'codbairro', 'option_label' => 'nomebairro', 'source' => 'bairro.codbairro'],
            ['name' => 'codcep', 'label' => 'CEP', 'type' => 'select', 'required' => true, 'options_resource' => 'ceps', 'option_value' => 'codcep', 'option_label' => 'numeroCep', 'source' => 'cep.codcep'],
            ['name' => 'codcidade', 'label' => 'Cidade', 'type' => 'select', 'required' => true, 'options_resource' => 'cidades', 'option_value' => 'codcidade', 'option_label' => 'nomecidade', 'source' => 'cidade.codcidade'],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codcliente'],
            ['label' => 'Cliente', 'path' => 'nomecliente'],
            ['label' => 'Sexo', 'path' => 'sexo.nomesexo'],
            ['label' => 'Cidade', 'path' => 'cidade.nomecidade'],
            ['label' => 'Rua', 'path' => 'rua.nomerua'],
        ],
    ],
    'fornecedores' => [
        'label' => 'Fornecedores',
        'singular' => 'Fornecedor',
        'endpoint' => '/fornecedores',
        'primary_keys' => ['codfornecedor'],
        'summary' => 'Fornecedores com contato e endereco.',
        'fields' => [
            ['name' => 'nomefornecedor', 'label' => 'Nome do Fornecedor', 'type' => 'text', 'required' => true],
            ['name' => 'codrua', 'label' => 'Rua', 'type' => 'select', 'required' => true, 'options_resource' => 'ruas', 'option_value' => 'codrua', 'option_label' => 'nomerua', 'source' => 'rua.codrua'],
            ['name' => 'codbairro', 'label' => 'Bairro', 'type' => 'select', 'required' => true, 'options_resource' => 'bairros', 'option_value' => 'codbairro', 'option_label' => 'nomebairro', 'source' => 'bairro.codbairro'],
            ['name' => 'codcep', 'label' => 'CEP', 'type' => 'select', 'required' => true, 'options_resource' => 'ceps', 'option_value' => 'codcep', 'option_label' => 'numeroCep', 'source' => 'cep.codcep'],
            ['name' => 'codcidade', 'label' => 'Cidade', 'type' => 'select', 'required' => true, 'options_resource' => 'cidades', 'option_value' => 'codcidade', 'option_label' => 'nomecidade', 'source' => 'cidade.codcidade'],
            ['name' => 'telefonefornecedor', 'label' => 'Telefone', 'type' => 'text', 'required' => true],
            ['name' => 'emailfornecedor', 'label' => 'E-mail', 'type' => 'email', 'required' => true],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codfornecedor'],
            ['label' => 'Fornecedor', 'path' => 'nomefornecedor'],
            ['label' => 'Cidade', 'path' => 'cidade.nomecidade'],
            ['label' => 'Telefone', 'path' => 'telefonefornecedor'],
            ['label' => 'E-mail', 'path' => 'emailfornecedor'],
        ],
    ],
    'produtos' => [
        'label' => 'Produtos',
        'singular' => 'Produto',
        'endpoint' => '/produtos',
        'primary_keys' => ['codproduto'],
        'summary' => 'Produtos com tipo, marca e fornecedor.',
        'fields' => [
            ['name' => 'nomeproduto', 'label' => 'Nome do Produto', 'type' => 'text', 'required' => true],
            ['name' => 'codtipo', 'label' => 'Tipo', 'type' => 'select', 'required' => true, 'options_resource' => 'tipos', 'option_value' => 'codtipo', 'option_label' => 'nometipo', 'source' => 'tipo.codtipo'],
            ['name' => 'codmarca', 'label' => 'Marca', 'type' => 'select', 'required' => true, 'options_resource' => 'marcas', 'option_value' => 'codmarca', 'option_label' => 'nomemarca', 'source' => 'marca.codmarca'],
            ['name' => 'quantidade', 'label' => 'Quantidade', 'type' => 'number', 'required' => true],
            ['name' => 'valor', 'label' => 'Valor', 'type' => 'number', 'required' => true, 'step' => '0.01'],
            ['name' => 'codfornecedor', 'label' => 'Fornecedor', 'type' => 'select', 'required' => true, 'options_resource' => 'fornecedores', 'option_value' => 'codfornecedor', 'option_label' => 'nomefornecedor', 'source' => 'fornecedor.codfornecedor'],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codproduto'],
            ['label' => 'Produto', 'path' => 'nomeproduto'],
            ['label' => 'Tipo', 'path' => 'tipo.nometipo'],
            ['label' => 'Marca', 'path' => 'marca.nomemarca'],
            ['label' => 'Quantidade', 'path' => 'quantidade'],
            ['label' => 'Valor', 'path' => 'valor', 'format' => 'currency'],
        ],
    ],
    'compras' => [
        'label' => 'Compras',
        'singular' => 'Compra',
        'endpoint' => '/compras',
        'primary_keys' => ['codcompra'],
        'summary' => 'Compras realizadas por clientes.',
        'fields' => [
            ['name' => 'datacompra', 'label' => 'Data da Compra', 'type' => 'date', 'required' => true],
            ['name' => 'codcliente', 'label' => 'Cliente', 'type' => 'select', 'required' => true, 'options_resource' => 'clientes', 'option_value' => 'codcliente', 'option_label' => 'nomecliente', 'source' => 'cliente.codcliente'],
        ],
        'columns' => [
            ['label' => 'Codigo', 'path' => 'codcompra'],
            ['label' => 'Data', 'path' => 'datacompra'],
            ['label' => 'Cliente', 'path' => 'cliente.nomecliente'],
        ],
    ],
    'compras-produtos' => [
        'label' => 'Itens de Compra',
        'singular' => 'Item de Compra',
        'endpoint' => '/compras-produtos',
        'primary_keys' => ['codcompra', 'codproduto'],
        'summary' => 'Itens que ligam uma compra a um produto.',
        'fields' => [
            ['name' => 'codcompra', 'label' => 'Compra', 'type' => 'select', 'required' => true, 'options_resource' => 'compras', 'option_value' => 'codcompra', 'option_label' => 'codcompra', 'source' => 'compra.codcompra'],
            ['name' => 'codproduto', 'label' => 'Produto', 'type' => 'select', 'required' => true, 'options_resource' => 'produtos', 'option_value' => 'codproduto', 'option_label' => 'nomeproduto', 'source' => 'produto.codproduto'],
            ['name' => 'quantidade', 'label' => 'Quantidade', 'type' => 'number', 'required' => true],
            ['name' => 'valorcp', 'label' => 'Valor do Item', 'type' => 'number', 'required' => true, 'step' => '0.01'],
        ],
        'columns' => [
            ['label' => 'Compra', 'path' => 'compra.codcompra'],
            ['label' => 'Produto', 'path' => 'produto.nomeproduto'],
            ['label' => 'Quantidade', 'path' => 'quantidade'],
            ['label' => 'Valor', 'path' => 'valorcp', 'format' => 'currency'],
        ],
    ],
];

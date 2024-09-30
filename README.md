Verificador de emails para PHP

Forma de uso:

    $check = new Mailcheck();

    $check->check(email, opções);

O único parâmetro necessário é o email. Se somente o email for informado, a verificação feita será a estrutura do email, permitindo uma string da seguinte maneira: [A-Z a-z 1-9 . _ % + - ç Ç] + @ + [a-z A-Z 0-9 . -] + [a-z A-Z] (pelo menos 2 dígitos). Pode conter qualquer letras, símbolos e números dentro dos colchetes, seguindo a estrutura informada.

O retorno pode ser true, caso o email passe na verificação, e false caso não passe na verificação.

Pode ser usado 3 opções para a verificação de email, que são as seguintes:

  $check->check(email, 1) : 
  
   A opção 1 permite a mesma estrutura anterior, mas o final do email deve conter .com ou .com.br ou .br, qualque email que não tenha essas terminações será recusado.

  $check->check(email, 2) : 
  
   A opção 2 permite a mesma estrutura da primeira parte ( [A-Z a-z 1-9 . _ % + - ç Ç] ) seguido dos seguintes provedores de email:(gmail.com|outlook.com|outlook.com.br|hotmail.com|hotmail.com.br|live.com|live.com.br|yahoo.com|yahoo.com.br|terra.com|terra.com.br|icloud.com|uol.com.br|myyahoo.com|myyahoo.com.br). 
  Essa opção é útil para permitir somente emails pessoais.

  $check->check(email, [array ou string separada por | ou vírgula): 
  
   A última opção permite a mesma estrutura da primeira parte ( [A-Z a-z 1-9 . _ % + - ç Ç] ) e a estrutura restante pode ser informada por meio de um array ou string separada por | ou vírgula, por exemplo: 
  
      $check->check(email, array(@gmail.com, @hotmail.com);
      $check->check(email, "@gmail.com, @hotmail.com");
      $check->check(email, "@gmail.com|@hotmail.com");

A biblioteca pode ser implementada no CodeIgniter 3, adicionando o arquivo no diretório: application/libraries. Após isso é só carregar a biblioteca e chamar a função:

    $this->load->library('mailcheck');
    $this->mailcheck->check(email, opções);

define ->
  return ->
    letters = 'ABCDEFGHJKMNPQRSTUVWXYZ'
    numbers = '23456789'

    rlet = Math.floor Math.random() * letters.length
    pass = letters.substring rlet, rlet + 1

    for i in [0...5]
      rnum = Math.floor Math.random() * numbers.length
      pass += numbers.substring rnum, rnum+1

    return pass
